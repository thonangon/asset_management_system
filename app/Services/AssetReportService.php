<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Warranty;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use App\Models\AssetReport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
/**
 * Builds the asset management report. This service is the single source of
 * truth: the JSON endpoint, the Excel export and the PDF export all consume
 * the exact same array, so a figure can never differ between screen and file.
 */
class AssetReportService
{
    /**
     * @return array<string, mixed>
     */
    public function build(
        CarbonImmutable $periodStart,
        CarbonImmutable $periodEnd,
        ?int $categoryId = null,
        ?int $assetId = null,
    ): array {
        $rows = $this->rows($periodStart, $periodEnd, $categoryId, $assetId);

        return [
            'period' => [
                'start_date' => $periodStart->toDateString(),
                'end_date' => $periodEnd->toDateString(),
                'days' => (int) round($periodStart->diffInDays($periodEnd)) + 1,
                'months' => (($periodEnd->year - $periodStart->year) * 12)
                    + ($periodEnd->month - $periodStart->month) + 1,
            ],
            'filters' => [
                'start_date' => $periodStart->toDateString(),
                'end_date' => $periodEnd->toDateString(),
                'category_id' => $categoryId,
                'asset_id' => $assetId,
            ],
            'summary' => $this->totals($rows),
            'groups' => $this->groups($rows),
            'rows' => $rows,
            'generated_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Flat, per-asset report rows ordered by category name then asset name.
     *
     * @return array<int, array<string, mixed>>
     */
    public function rows(
        CarbonImmutable $periodStart,
        CarbonImmutable $periodEnd,
        ?int $categoryId = null,
        ?int $assetId = null,
    ): array {
        return $this->query($periodEnd, $categoryId, $assetId)
            ->get()
            ->map(fn (Asset $asset) => $this->row($asset, $periodStart, $periodEnd))
            // sortBy() is stable, so the name sort acts as the tie breaker.
            ->sortBy(fn (array $row) => mb_strtolower((string) $row['asset_name']))
            ->sortBy(fn (array $row) => $row['category_name'] === null
                ? '~~uncategorized'
                : mb_strtolower((string) $row['category_name']))
            ->values()
            ->all();
    }

    /**
     * @return Builder<Asset>
     */
    private function query(CarbonImmutable $periodEnd, ?int $categoryId, ?int $assetId): Builder
    {
        return Asset::query()
            ->with([
                'assetCategory',
                'warranty',
                'transfers' => fn (Relation $query) => $query
                    ->whereDate('TransferDate', '<=', $periodEnd->toDateString())
                    ->orderByDesc('TransferDate'),
            ])
            ->when($categoryId !== null, fn (Builder $query) => $query->where('assets.asset_category_id', $categoryId))
            ->when($assetId !== null, fn (Builder $query) => $query->where('assets.id', $assetId))
            // An asset is in scope when it was already acquired on the closing date.
            // Rows with a NULL purchase_date are dropped by this comparison.
            ->whereDate('assets.purchase_date', '<=', $periodEnd->toDateString())
            ->orderBy('assets.name');
    }

    /**
     * @return array<string, mixed>
     */
    private function row(Asset $asset, CarbonImmutable $periodStart, CarbonImmutable $periodEnd): array
    {
        $transfer = $asset->transfers->first();
        $warranty = $asset->warranty;
        $status = $this->warrantyStatus($warranty, $periodEnd);

        return [
            'asset_id' => $asset->id,
            'asset_tag' => $asset->asset_tag,
            'serial_number' => $asset->serial_number,
            'asset_name' => $asset->name,
            'asset_status' => $asset->status,
            'category_id' => $asset->asset_category_id,
            'category_name' => $asset->assetCategory?->name,
            'acquisition_date' => $asset->purchase_date?->toDateString(),
            'transfer_date' => $transfer?->TransferDate?->toDateString(),
            'transfer_status' => $transfer?->Status,
            ...DepreciationCalculator::make(
                $asset->purchase_cost,
                $asset->useful_life_years,
                $asset->purchase_date,
            )->report($periodStart, $periodEnd),
            'warranty_status' => $status,
            'warranty' => $warranty ? [
                'id' => $warranty->id,
                'provider' => $warranty->provider,
                'start_date' => $warranty->start_date?->toDateString(),
                'end_date' => $warranty->end_date?->toDateString(),
                'policy_number' => $warranty->policy_number,
                'coverage_details' => $warranty->coverage_details,
                'recorded_status' => $warranty->status,
                'status' => $status,
            ] : null,
        ];
    }

    /**
     * Warranty status evaluated as of the reporting closing date.
     */
    private function warrantyStatus(?Warranty $warranty, CarbonImmutable $periodEnd): string
    {
        if (! $warranty) {
            return 'none';
        }

        if ($warranty->start_date && $periodEnd->lessThan($warranty->start_date)) {
            return 'not_yet_active';
        }

        if ($warranty->end_date && $periodEnd->greaterThan($warranty->end_date)) {
            return 'expired';
        }

        return 'active';
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    public function totals(array $rows): array
    {
        $sum = fn (string $key) => round(collect($rows)->sum(fn (array $row) => (float) $row[$key]), 2);

        return [
            'asset_count' => count($rows),
            'historical_cost' => $sum('historical_cost'),
            'opening_net_book_value' => $sum('opening_net_book_value'),
            'period_depreciation' => $sum('period_depreciation'),
            'current_year_depreciation' => $sum('current_year_depreciation'),
            'total_accumulated_depreciation' => $sum('total_accumulated_depreciation'),
            'current_month_depreciation' => $sum('current_month_depreciation'),
            'ending_net_book_value' => $sum('ending_net_book_value'),
            'fully_depreciated_count' => count(array_filter($rows, fn (array $row) => $row['is_fully_depreciated'] === true)),
            'active_warranty_count' => count(array_filter($rows, fn (array $row) => $row['warranty_status'] === 'active')),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function groups(array $rows): array
    {
        return collect($rows)
            ->groupBy(fn (array $row) => $row['category_id'] === null
                ? 'uncategorized'
                : (string) $row['category_id'])
            ->map(fn (Collection $group, string $key) => [
                'category' => $group[0]['category_id'] === null ? null : [
                    'id' => $group[0]['category_id'],
                    'name' => $group[0]['category_name'],
                ],
                'category_id' => $group[0]['category_id'],
                'category_name' => $group[0]['category_name'] ?? 'Uncategorized',
                'asset_count' => count($group),
                'assets' => $group->all(),
                'totals' => $this->totals($group->all()),
            ])
            ->values()
            ->all();
    }
    /**
     * @param  array<string, mixed>  $data
     */
    public function record(array $data, ?int $userId): AssetReport
    {
        return AssetReport::create([
            'requested_by_user_id' => $userId,
            'report_type' => $data['report_type'],
            'parameters' => $this->parameters($data),
            'status' => 'pending',
            'format' => $data['format'] ?? 'json',
            'requested_at' => now(),
        ]);
    }

    public function history(int $perPage = 15): LengthAwarePaginator
    {
        return AssetReport::query()
            ->orderByDesc('requested_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function parameters(array $data): array
    {
        $parameters = [
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
        ];

        foreach (['department', 'category', 'status', 'transaction_type'] as $filter) {
            if (array_key_exists($filter, $data) && $data[$filter] !== null && $data[$filter] !== '') {
                $parameters[$filter] = $data[$filter];
            }
        }

        return $parameters;
    }
}
