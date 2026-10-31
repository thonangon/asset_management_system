<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use DateTimeInterface;

/**
 * Straight-line depreciation calculator.
 *
 * Depreciation years are anchored on the acquisition date (anniversary based),
 * which mirrors the annual schedule written by AssetController::buildDepreciationRecords().
 * Every figure in the report is a difference of the monotone accrual curve
 * returned by accumulatedAt(), so a day is never counted twice and never missed.
 */
class DepreciationCalculator
{
    private readonly float $cost;

    private readonly int $usefulLifeYears;

    private readonly CarbonImmutable $acquisitionDate;

    public function __construct(float $cost, int $usefulLifeYears, CarbonImmutable $acquisitionDate)
    {
        $this->cost = max(0.0, round($cost, 2));
        $this->usefulLifeYears = max(0, $usefulLifeYears);
        $this->acquisitionDate = $acquisitionDate->startOfDay();
    }

    public static function make(mixed $cost, mixed $usefulLifeYears, mixed $acquisitionDate): self
    {
        return new self(
            (float) ($cost ?? 0),
            (int) ($usefulLifeYears ?? 0),
            self::toImmutable($acquisitionDate),
        );
    }

    private static function toImmutable(mixed $date): CarbonImmutable
    {
        if ($date instanceof CarbonImmutable) {
            return $date->startOfDay();
        }

        if ($date instanceof DateTimeInterface) {
            return CarbonImmutable::instance($date)->startOfDay();
        }

        if ($date === null || $date === '') {
            throw new \InvalidArgumentException('An acquisition date is required to calculate depreciation.');
        }

        return CarbonImmutable::parse((string) $date)->startOfDay();
    }

    /**
     * Historical cost (assets.purchase_cost). There is no residual/salvage
     * column in the schema, so residual value is assumed to be zero.
     */
    public function cost(): float
    {
        return round($this->cost, 2);
    }

    public function usefulLifeYears(): int
    {
        return $this->usefulLifeYears;
    }

    public function acquisitionDate(): CarbonImmutable
    {
        return $this->acquisitionDate;
    }

    /**
     * Cost divided by useful life, rounded to two decimals exactly like the
     * persisted annual schedule. Every full year except the last one uses this.
     */
    public function straightLineAnnualAmount(): float
    {
        if ($this->cost <= 0 || $this->usefulLifeYears <= 0) {
            return 0.0;
        }

        return round($this->cost / $this->usefulLifeYears, 2);
    }

    public function straightLineMonthlyAmount(): float
    {
        if ($this->cost <= 0 || $this->usefulLifeYears <= 0) {
            return 0.0;
        }

        return round($this->straightLineAnnualAmount() / 12, 2);
    }

    public function annualRatePercent(): float
    {
        return $this->usefulLifeYears > 0 ? round(100 / $this->usefulLifeYears, 2) : 0.0;
    }

    public function monthlyRatePercent(): float
    {
        return $this->usefulLifeYears > 0 ? round((100 / $this->usefulLifeYears) / 12, 2) : 0.0;
    }

    /**
     * Depreciation booked in depreciation year $yearIndex (0 based).
     * The final year absorbs the rounding remainder so the schedule always
     * closes at exactly zero net book value.
     */
    public function annualAmount(int $yearIndex): float
    {
        if ($yearIndex < 0 || $yearIndex >= $this->usefulLifeYears) {
            return 0.0;
        }

        $annual = $this->straightLineAnnualAmount();

        if ($yearIndex < $this->usefulLifeYears - 1) {
            return $annual;
        }

        return max(0.0, round($this->cost - ($annual * ($this->usefulLifeYears - 1)), 2));
    }

    public function yearStart(int $yearIndex): CarbonImmutable
    {
        return $this->acquisitionDate->addYears(max(0, $yearIndex));
    }

    public function yearEnd(int $yearIndex): CarbonImmutable
    {
        return $this->acquisitionDate->addYears(max(0, $yearIndex) + 1)->subDay();
    }

    public function daysInYear(int $yearIndex): int
    {
        return $this->inclusiveDays($this->yearStart($yearIndex), $this->yearEnd($yearIndex));
    }

    /**
     * Number of days between two dates, both inclusive.
     */
    private function inclusiveDays(CarbonImmutable $from, CarbonImmutable $to): int
    {
        return (int) round($from->diffInDays($to)) + 1;
    }

    /**
     * Last day on which the asset still carries a book value.
     */
    public function fullyDepreciatedDate(): ?CarbonImmutable
    {
        if ($this->cost <= 0 || $this->usefulLifeYears <= 0) {
            return null;
        }

        return $this->yearEnd($this->usefulLifeYears - 1);
    }

    /**
     * Depreciation year (0 based) that contains $date.
     */
    public function yearIndexAt(mixed $date): int
    {
        if ($this->usefulLifeYears <= 0) {
            return 0;
        }

        $asOf = $date instanceof DateTimeInterface
            ? CarbonImmutable::instance($date)
            : CarbonImmutable::parse((string) $date);

        if ($asOf->lessThan($this->acquisitionDate)) {
            return 0;
        }

        $index = (int) intdiv((int) $this->acquisitionDate->diffInMonths($asOf), 12);
        $index = max(0, min($index, $this->usefulLifeYears - 1));

        // diffInMonths() truncates, so nudge the guess onto the exact year for
        // edge cases such as a 29 February acquisition date.
        while ($index > 0 && $asOf->lessThan($this->yearStart($index))) {
            $index--;
        }

        while ($index < $this->usefulLifeYears - 1 && $asOf->greaterThan($this->yearEnd($index))) {
            $index++;
        }

        return $index;
    }

    /**
     * Cumulative depreciation charged from the acquisition date through $date
     * (inclusive), pro-rated per day inside each depreciation year and capped at
     * the historical cost.
     */
    public function accumulatedAt(mixed $date): float
    {
        if ($this->cost <= 0 || $this->usefulLifeYears <= 0) {
            return 0.0;
        }

        $asOf = self::toImmutable($date);

        if ($asOf->lessThan($this->acquisitionDate)) {
            return 0.0;
        }

        $total = 0.0;

        for ($yearIndex = 0; $yearIndex < $this->usefulLifeYears; $yearIndex++) {
            $from = $this->yearStart($yearIndex);
            $yearEnd = $this->yearEnd($yearIndex);

            if ($yearEnd->lessThan($asOf)) {
                $to = $yearEnd;
            } else {
                $to = $asOf;
            }

            if ($to->lessThan($from)) {
                break;
            }

            $total += $this->annualAmount($yearIndex)
                * ($this->inclusiveDays($from, $to) / $this->daysInYear($yearIndex));

            if ($asOf->lessThanOrEqualTo($yearEnd)) {
                break;
            }
        }

        return round(min($total, $this->cost), 2);
    }

    public function netBookValueAt(mixed $date): float
    {
        return round(max(0.0, $this->cost - $this->accumulatedAt($date)), 2);
    }

    public function isFullyDepreciatedAt(mixed $date): bool
    {
        return $this->cost > 0 && $this->accumulatedAt($date) >= $this->cost;
    }

    /**
     * Depreciation between two dates, both inclusive.
     */
    public function depreciationBetween(mixed $from, mixed $to): float
    {
        $start = self::toImmutable($from);
        $end = self::toImmutable($to);

        if ($end->lessThan($start)) {
            return 0.0;
        }

        return round($this->accumulatedAt($end) - $this->accumulatedAt($start->subDay()), 2);
    }

    /**
     * Full report row of depreciation figures for one asset over one reporting period.
     *
     * @return array<string, mixed>
     */
    public function report(CarbonImmutable $periodStart, CarbonImmutable $periodEnd): array
    {
        $accumulatedAtEnd = $this->accumulatedAt($periodEnd);
        $accumulatedBeforePeriod = $this->accumulatedAt($periodStart->subDay());

        $yearIndex = $this->yearIndexAt($periodEnd);
        $depreciationYearStart = $this->yearStart($yearIndex);
        $currentYearStart = $depreciationYearStart->greaterThan($periodStart)
            ? $depreciationYearStart
            : $periodStart;

        $monthStart = $periodEnd->startOfMonth();
        $currentMonthStart = $monthStart->greaterThan($periodStart) ? $monthStart : $periodStart;

        return [
            'historical_cost' => $this->cost(),
            'useful_life_years' => $this->usefulLifeYears,
            'straight_line_rate_year' => $this->annualRatePercent(),
            'straight_line_rate_year_amount' => $this->straightLineAnnualAmount(),
            'straight_line_rate_month' => $this->monthlyRatePercent(),
            'straight_line_rate_month_amount' => $this->straightLineMonthlyAmount(),
            'accumulated_before_period' => $accumulatedBeforePeriod,
            'opening_net_book_value' => round(max(0.0, $this->cost - $accumulatedBeforePeriod), 2),
            'period_depreciation' => round($accumulatedAtEnd - $accumulatedBeforePeriod, 2),
            'current_year_depreciation' => round($accumulatedAtEnd - $this->accumulatedAt($currentYearStart->subDay()), 2),
            'total_accumulated_depreciation' => $accumulatedAtEnd,
            'current_month_depreciation' => round($accumulatedAtEnd - $this->accumulatedAt($currentMonthStart->subDay()), 2),
            'ending_net_book_value' => round(max(0.0, $this->cost - $accumulatedAtEnd), 2),
            'current_depreciation_year' => $yearIndex + 1,
            'current_depreciation_period' => [
                'start' => $depreciationYearStart->toDateString(),
                'end' => $this->yearEnd($yearIndex)->toDateString(),
            ],
            'fully_depreciated_date' => $this->fullyDepreciatedDate()?->toDateString(),
            'is_fully_depreciated' => $this->cost > 0 && $accumulatedAtEnd >= $this->cost,
        ];
    }
}
