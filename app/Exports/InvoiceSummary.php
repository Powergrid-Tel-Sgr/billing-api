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

class InvoiceSummary implements FromCollection, WithHeadings, WithStrictNullComparison, WithColumnFormatting, ShouldAutoSize, WithStyles, WithColumnWidths, WithTitle
{
    protected $billGroup;
    protected $vendor;
    public $rowCount;

    public function __construct($billGroup, $vendor)
    {
        $this->billGroup = $billGroup;
        $this->vendor = $vendor;
    }

    public function headings(): array
    {
        return [
            [
                "Invoice No:- _",
                "",
                "",
                "POWERGRID CORPORATION OF INDIA LIMITED",
                "",
                "",
                "",
                "",
                "",
                json_decode($this->vendor->meta)->po ?? "PO: 5200052646 Dated: 08-04-2022",
                "",
                "",
            ],
            [
                "INVOICE DATE:- " . Carbon::now()->format("d-m-y"),
                "",
                "",
                "GST NO: 01AAACP0252G1Z7",
                "",
                "",
                "",
                "",
                "",
                "Period",
                Carbon::create()->month($this->billGroup[0]->month + 1)->startOfMonth()->year(Carbon::now()->year)->format('d.m.y'),
                Carbon::create()->month($this->billGroup[count($this->billGroup) - 1]->month + 1)->endOfMonth()->year(Carbon::now()->year)->format('d.m.y'),
            ],
            [
                "S No",
                "Customer Name",
                "CP Number",
                "Capacity",
                "Link From",
                "Link To",
                "DOCO",
                "Annual Invoice Value",
                $this->vendor->name . " Share Percent",
                "Unit Rate",
                "Number Of Days",
                "Amount (excluding GST)",
            ]
        ];
    }

    public function collection()
    {
        $billTable = $this->billGroup[0]->billTable;
        $billRow = [];
        $grandTotal = 0;
        foreach ($billTable as $row) {

            $days = collect($this->billGroup)->reduce(function ($b, $c) use ($row) {
                $item = collect($c->billTable)->first(function ($val) use ($row) {
                    return $val->cpNumber == $row->cpNumber;
                });
                return $b + $item->numberOfDays;
            });

            $total = round(($row->unitRate * $days), 2);
            $grandTotal += $total;
            $billRow[] = [
                $row->index,
                $row->name,
                $row->cpNumber,
                $row->capacity,
                $row->linkFrom,
                $row->linkTo,
                $row->doco,
                $row->annualInvoiceValue,
                $row->sharePercent,
                $row->unitRate,
                $days,
                $total,
            ];
        }

        $billRow[] = ["Total (excluding GST)", "", "", "", "", "", "", "", "", "", "", $grandTotal];
        $this->rowCount = count($billRow) + 3;

        return collect($billRow);
    }

    public function title(): string
    {
        return 'Invoice';
    }

    public function columnFormats(): array
    {
        return [
            'H' => '"₹ "#,##0',
            'J' => '"₹ "#,##0.00_-',
            'L' => '"₹ "#,##0.00_-',
            'I' => NumberFormat::FORMAT_PERCENTAGE,
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

        $sheet->getStyle("A1:L{$this->rowCount}")->applyFromArray($styleArray);

        $sheet->getStyle('A1:L3')->getFont()->setBold(true);
        $sheet->getStyle('1:2')->getFont()->setSize(12);
        $sheet->getStyle('A1:L3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A3:L3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A3:L3')->getAlignment()->setWrapText(true);
        $sheet->mergeCells('A1:C1');
        $sheet->mergeCells('D1:I1');
        $sheet->mergeCells('J1:L1');
        $sheet->mergeCells('A2:C2');
        $sheet->mergeCells('D2:I2');
        $sheet->mergeCells("A" . ($this->rowCount) . ":K" . ($this->rowCount));
        $sheet->getStyle("A" . ($this->rowCount) . ":L" . ($this->rowCount))->getFont()->setBold(true);;
        $sheet->getStyle("A" . ($this->rowCount) . ":K" . ($this->rowCount))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setFitToWidth(true);
        $sheet->getPageMargins()->setTop(0.2);
        $sheet->getPageMargins()->setLeft(0.2);
        $sheet->getPageMargins()->setRight(0.2);
        $sheet->getPageMargins()->setBottom(0.2);
    }

    public function columnWidths(): array
    {
        return [
            'H' => 15,
            'I' => 10,
            'K' => 10,
            'L' => 15,
        ];
    }
}
