<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SummarySheet implements FromCollection, WithHeadings, WithTitle, WithStrictNullComparison, WithColumnFormatting, ShouldAutoSize, WithStyles, WithColumnWidths
{
    protected $bill;
    protected $vendor;
    protected $index;
    public $itemCount;

    public function __construct($bill, $vendor, $index)
    {
        $this->bill = $bill;
        $this->vendor = $vendor;
        $this->index = $index;
    }

    public function romanize($num)
    {
        switch ($num) {
            case 1:
                return "I";
            case 2:
                return "II";
            case 3:
                return "III";
            case 4:
                return "IV";
            case 5:
                return "V";
            default:
                return "_";
        }
    }

    public function headings(): array
    {
        return [
            [
                "Details of Bills for Payment to " . $this->vendor->name . " as per Revenue Sharing Agreement.",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "Annex - " . $this->romanize($this->index),
                "",
                "",
                "",
                "",
                "",
            ],
            [
                "Bill for " . Carbon::create()->month($this->bill->month + 1)->format('F') . ", " . Carbon::now()->year,
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "Period",
                Carbon::create()->month($this->bill->month + 1)->startOfMonth()->year(Carbon::now()->year)->format('d.m.y') .
                    " — " .
                    Carbon::create()->month($this->bill->month + 1)->endOfMonth()->year(Carbon::now()->year)->format('d.m.y'),
                "",
                "",
                "",
                "",
            ],
            [
                "S No",
                "Customer Name",
                "CP Number",
                "Capacity",
                "DOCO",
                "Annual Invoice Value",
                "Sanguine Share Percent",
                "Discount Offered",
                "Annual Vendor Value",
                "Unit Rate",
                "Number Of Days",
                "Downtime (Hours)",
                "Uptime Percent",
                "Penalty Factor",
                "Penalty (Hours)",
                "Penalty Amount",
                "Amount (excluding GST)",
            ]
        ];
    }

    public function collection()
    {
        $billTable = $this->bill->billTable;
        $this->itemCount = count($billTable) + 6;
        $totalPenalty = collect($billTable)->reduce(fn ($a, $c) => $a + $c->penaltyAmount);
        $billRow = [];

        foreach ($billTable as $row) {
            $billRow[] = [
                $row->index,
                $row->name,
                $row->cpNumber,
                $row->capacity,
                $row->doco,
                $row->annualInvoiceValue,
                $row->sharePercent,
                $row->discountOffered,
                $row->annualVendorValue,
                $row->unitRate,
                $row->numberOfDays,
                $row->downtime,
                $row->uptimePercent,
                $row->penaltySlab,
                $row->penaltyHours,
                $row->penaltyAmount,
                $row->amount,
            ];
        }

        $billRow[] = ["Total (excluding GST)", "", "", "", "", "", "", "", "", "", "", "", "", "", "", $totalPenalty, $this->bill->billTotal];
        $billRow[] = ["GST Amount", "", "", "", "", "", "", "", "", "", "", "", "", "", "", round(($totalPenalty * 0.18), 2), round(($this->bill->billTotal * 0.18), 2)];
        $billRow[] = ["Total (including GST)", "", "", "", "", "", "", "", "", "", "", "", "", "", "", round(($totalPenalty * 1.18), 2), round(($this->bill->billTotal * 1.18), 2)];

        return collect($billRow);
    }

    public function title(): string
    {
        return 'Itemwise_' . Carbon::create()->month($this->bill->month + 1)->format("F");
    }

    public function columnFormats(): array
    {
        return [
            'F' => '"₹ "#,##0',
            'I' => '"₹ "#,##0.00_-',
            'P' => '"₹ "#,##0.00_-',
            'Q' => '"₹ "#,##0.00_-',
            'G' => NumberFormat::FORMAT_PERCENTAGE,
            'M' => NumberFormat::FORMAT_PERCENTAGE_00,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                ]
            ],
        ];

        $sheet->getStyle("A1:Q{$this->itemCount}")->applyFromArray($styleArray);

        $sheet->getStyle('A1:Q3')->getFont()->setBold(true);
        $sheet->getStyle('A1:Q3')->getFont()->setBold(true);
        $sheet->getStyle('1')->getFont()->setSize(14);
        $sheet->getStyle('A1:Q3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A3:Q3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A3:Q3')->getAlignment()->setWrapText(true);
        $sheet->mergeCells('A1:K1');
        $sheet->mergeCells('L1:Q1');
        $sheet->mergeCells('A2:K2');
        $sheet->mergeCells('M2:Q2');
        $sheet->mergeCells("A" . ($this->itemCount - 2) . ":O" . ($this->itemCount - 2));
        $sheet->mergeCells("A" . ($this->itemCount - 1) . ":O" . ($this->itemCount - 1));
        $sheet->mergeCells("A" . ($this->itemCount) . ":O" . ($this->itemCount));
        $sheet->getStyle("A" . ($this->itemCount - 2) . ":Q" . ($this->itemCount - 2))->getFont()->setBold(true);;
        $sheet->getStyle("A" . ($this->itemCount - 1) . ":Q" . ($this->itemCount - 1))->getFont()->setBold(true);;
        $sheet->getStyle("A" . ($this->itemCount) . ":Q" . ($this->itemCount))->getFont()->setBold(true);;
        $sheet->getStyle("A" . ($this->itemCount - 2) . ":O" . ($this->itemCount - 2))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("A" . ($this->itemCount - 1) . ":O" . ($this->itemCount - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("A" . ($this->itemCount) . ":O" . ($this->itemCount))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("N")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageMargins()->setTop(0.2);
        $sheet->getPageMargins()->setLeft(0.2);
        $sheet->getPageMargins()->setRight(0.2);
        $sheet->getPageMargins()->setBottom(0.2);
    }

    public function columnWidths(): array
    {
        return [
            'F' => 15,
            'G' => 10,
            'H' => 10,
            'I' => 15,
            'J' => 12,
            'K' => 10,
            'L' => 10,
            'M' => 10,
            'N' => 10,
            'O' => 10,
            'P' => 15,
            'Q' => 15,
        ];
    }

    // public function registerEvents(): array
    // {
    //     return [
    //         AfterSheet::class => function (AfterSheet $event) {
    //             $event->sheet->mergeCells('A1:C1');
    //         },
    //     ];
    // }
}
