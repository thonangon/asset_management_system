<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Renders the array produced by AssetReportService: one row per asset,
 * a subtotal row per category and a grand total row at the bottom.
 */
class AssetReportExport implements FromArray, ShouldAutoSize, WithColumnFormatting, WithEvents, WithStyles
{
    /** @var array<int, string> */
    public const HEADINGS = [
        'Report Start Date',
        'Report End Date',
        'Asset Category',
        'Asset Name',
        'Acquisition Date',
        'Transfer Date',
        'Historical Cost',
        'Useful Life (Years)',
        'Straight-Line Rate (Year) %',
        'Straight-Line Rate (Year) Amount',
        'Straight-Line Rate (Month) %',
        'Straight-Line Rate (Month) Amount',
        'Period Depreciation',
        'Current Year Depreciation',
        'Accumulated Depreciation',
        'Current Month Depreciation',
        'Ending NBV',
        'Warranty Provider',
        'Warranty Start Date',
        'Warranty End Date',
        'Warranty Status',
    ];

    /** @var array<int, int> Zero-based indexes of the money columns. */
    private const MONEY_COLUMNS = [6, 9, 11, 12, 13, 14, 15, 16];

    /** @var array<int, int> Zero-based indexes of the percentage columns. */
    private const PERCENT_COLUMNS = [8, 10];

    private const HEADING_ROW = 3;

    private const COLUMN_COUNT = 21;

    /**
     * @param  array<string, mixed>  $report  Result of AssetReportService::build().
     */
    public function __construct(private readonly array $report) {}

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        $rows = [
            ['Asset Management Report'],
            [
                'Report Start Date: '.$this->report['period']['start_date'],
                'Report End Date: '.$this->report['period']['end_date'],
            ],
            ['Generated At: '.($this->report['generated_at'] ?? now()->toDateTimeString())],
            self::HEADINGS,
        ];

        foreach ($this->report['groups'] as $group) {
            foreach ($group['assets'] as $asset) {
                $rows[] = $this->assetRow($asset);
            }

            $rows[] = $this->totalRow($group['category_name'] ?? 'Uncategorized', $group['totals'], 'Category Total');
        }

        $rows[] = $this->totalRow('All Categories', $this->report['summary'], 'Grand Total');

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $asset
     * @return array<int, mixed>
     */
    private function assetRow(array $asset): array
    {
        return [
            $this->report['period']['start_date'],
            $this->report['period']['end_date'],
            $asset['category_name'] ?? 'Uncategorized',
            $asset['asset_name'],
            $asset['acquisition_date'],
            $asset['transfer_date'] ?? '-',
            (float) $asset['historical_cost'],
            (int) $asset['useful_life_years'],
            (float) $asset['straight_line_rate_year'],
            (float) $asset['straight_line_rate_year_amount'],
            (float) $asset['straight_line_rate_month'],
            (float) $asset['straight_line_rate_month_amount'],
            (float) $asset['period_depreciation'],
            (float) $asset['current_year_depreciation'],
            (float) $asset['total_accumulated_depreciation'],
            (float) $asset['current_month_depreciation'],
            (float) $asset['ending_net_book_value'],
            $asset['warranty']['provider'] ?? '-',
            $asset['warranty']['start_date'] ?? '-',
            $asset['warranty']['end_date'] ?? '-',
            $asset['warranty_status'],
        ];
    }

    /**
     * @param  array<string, mixed>  $totals
     * @return array<int, mixed>
     */
    private function totalRow(string $label, array $totals, string $type): array
    {
        $row = array_fill(0, self::COLUMN_COUNT, null);
        $row[0] = $label;
        $row[1] = $type;
        $row[3] = $totals['asset_count'].' asset(s)';
        $row[6] = (float) $totals['historical_cost'];
        $row[12] = (float) $totals['period_depreciation'];
        $row[13] = (float) $totals['current_year_depreciation'];
        $row[14] = (float) $totals['total_accumulated_depreciation'];
        $row[15] = (float) $totals['current_month_depreciation'];
        $row[16] = (float) $totals['ending_net_book_value'];

        return $row;
    }

    /**
     * @return array<string, string>
     */
    public function columnFormats(): array
    {
        $formats = [
            'A' => 'yyyy-mm-dd',
            'B' => 'yyyy-mm-dd',
            'E' => 'yyyy-mm-dd',
            'F' => 'yyyy-mm-dd',
            'S' => 'yyyy-mm-dd',
            'T' => 'yyyy-mm-dd',
            'H' => '0',
        ];

        foreach (self::MONEY_COLUMNS as $index) {
            $formats[$this->columnLetter($index)] = '#,##0.00';
        }

        foreach (self::PERCENT_COLUMNS as $index) {
            $formats[$this->columnLetter($index)] = '0.00';
        }

        return $formats;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $worksheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            self::HEADING_ROW => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F3864']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => $this->borders(),
            ],
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function borders(): array
    {
        $outline = ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']];

        return ['allBorders' => $outline, 'outline' => $outline];
    }

    /**
     * @return array<class-string, callable>
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                // Maatwebsite\Excel\Sheet proxies the PhpSpreadsheet worksheet.
                $worksheet = $event->sheet;
                $lastRow = $worksheet->getHighestRow();

                // Keep the title block and the category / asset columns visible.
                $worksheet->freezePane('E'.(self::HEADING_ROW + 1));
                $worksheet->getRowDimension(self::HEADING_ROW)->setRowHeight(30);

                if ($lastRow <= self::HEADING_ROW) {
                    return;
                }

                foreach (range(self::HEADING_ROW + 1, $lastRow) as $rowNumber) {
                    $worksheet->getStyle("A{$rowNumber}:U{$rowNumber}")->applyFromArray($this->borders());
                }

                foreach ($this->totalRowNumbers($worksheet, $lastRow) as $rowNumber) {
                    $worksheet->getStyle("A{$rowNumber}:U{$rowNumber}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDCE6F1']],
                    ]);
                }
            },
        ];
    }

    /**
     * @return array<int, int>
     */
    private function totalRowNumbers(Sheet $worksheet, int $lastRow): array
    {
        $rows = [];

        foreach (range(self::HEADING_ROW + 1, $lastRow) as $rowNumber) {
            if (in_array($worksheet->getCell("B{$rowNumber}")->getValue(), ['Category Total', 'Grand Total'], true)) {
                $rows[] = $rowNumber;
            }
        }

        return $rows;
    }

    private function columnLetter(int $zeroBasedIndex): string
    {
        return chr(65 + $zeroBasedIndex);
    }
}
