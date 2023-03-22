<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CoverSheet implements FromCollection, WithHeadings, WithTitle, WithStrictNullComparison, ShouldAutoSize, WithStyles, WithColumnWidths, WithCustomStartCell, WithColumnFormatting
{
    protected $billGroup;
    protected $vendor;
    protected $groupTotal;
    public $itemCount;

    public function __construct($bill)
    {
        $this->billGroup = $bill->billGroup;
        $this->vendor = $bill->vendor;
        $this->groupTotal = $bill->billGroupTotal;
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

    public function startCell(): string
    {
        return 'B2';
    }

    public function headings(): array
    {
        return [
            [
                "Details of Bills for Payment to " . $this->vendor->name . " as per Revenue Sharing Agreement.",
                "",
            ],
            [
                "Period: " .
                    Carbon::create()->month($this->billGroup[0]->month + 1)->startOfMonth()->year(Carbon::now()->year)->format('d.m.y') .
                    " — " .
                    Carbon::create()->month($this->billGroup[count($this->billGroup) - 1]->month + 1)->endOfMonth()->year(Carbon::now()->year)->format('d.m.y'),
                "",
                "",
                "",
                "",
                json_decode($this->vendor->meta)->po ?? "PO: 5200052646 Dated: 08-04-2022",
                "",
                "",
            ],
        ];
    }

    public function collection()
    {

        $billRow = [
            ["", "", "", "", "", "", "", ""], ["", "", "", "", "", "", "", ""], ["", "", "", "", "", "", "", ""],
            ["S No", "Name", "Period", "Number of Days", "Amount without GST", "GST Amount (@18%)", "Amount with GST", "Remark"]
        ];
        $totalDays = 0;
        foreach ($this->billGroup as $index => $bg) {
            $days = Carbon::create()->month($bg->month + 1)->daysInMonth;
            $totalDays += $days;
            $billRow[] = [
                $index + 1,
                Carbon::create()->month($bg->month + 1)->startOfMonth()->year(Carbon::now()->year)->format('M-y'),
                Carbon::create()->month($bg->month + 1)->startOfMonth()->year(Carbon::now()->year)->format('d.m.y') .
                    " — " .
                    Carbon::create()->month($bg->month + 1)->endOfMonth()->year(Carbon::now()->year)->format('d.m.y'),
                $days,
                $bg->billTotal,
                round(($bg->billTotal * 0.18), 2),
                round(($bg->billTotal * 1.18), 2),
                "Item-wise Bill summary attached as Annex-" . $this->romanize($index + 1) . "",
            ];
        }

        $billRow[] = ["Total", "", "", $totalDays, $this->groupTotal, round(($this->groupTotal * 0.18), 2), round(($this->groupTotal * 1.18), 2)];
        // error_log(json_encode(json_decode($this->vendor->meta)->po));
        error_log(json_encode($this->vendor->meta));
        $billRow[] = [
            [""], [""], [""], [""], [""],
            ["", (json_decode($this->vendor->meta)->representative ?? "(Majid Shah)") . "\nM/s " . $this->vendor->name, "", "", "(Nasir Mufti)\nDeputy Manager\n(Tel-Srinagar)", "", "", "(Ajay Chaudhary)\nChief Manager\n(Tel-Jammu)"]
        ];

        return collect($billRow);
    }

    public function title(): string
    {
        return "Cover";
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

        $sheet->getStyle("B2:I3")->applyFromArray($styleArray);
        $sheet->getStyle("B7:I11")->applyFromArray($styleArray);
        $sheet->mergeCells('B2:I2');
        $sheet->mergeCells('B3:F3');
        $sheet->mergeCells('G3:I3');
        $sheet->mergeCells('B11:D11');

        $sheet->getStyle('B2:I7')->getFont()->setBold(true);
        $sheet->getStyle('11')->getFont()->setBold(true);
        $sheet->getStyle('2')->getFont()->setSize(14);
        $sheet->getStyle('7')->getAlignment()->setWrapText(true);
        $sheet->getStyle('17')->getAlignment()->setWrapText(true);

        $sheet->getStyle('B2:I11')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(40);
        $sheet->getRowDimension(3)->setRowHeight(40);
        $sheet->getRowDimension(7)->setRowHeight(40);
        $sheet->getRowDimension(8)->setRowHeight(40);
        $sheet->getRowDimension(9)->setRowHeight(40);
        $sheet->getRowDimension(10)->setRowHeight(40);
        $sheet->getRowDimension(11)->setRowHeight(40);
        $sheet->getRowDimension(14)->setRowHeight(60);
        $sheet->getStyle('2:11')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('17')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setVerticalCentered(true);
        $sheet->getPageSetup()->setHorizontalCentered(true);
    }

    public function columnWidths(): array
    {
        return [
            'E' => 10,
            'F' => 15,
            'G' => 15,
            'H' => 15,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => '"₹ "#,##0.00_-',
            'G' => '"₹ "#,##0.00_-',
            'H' => '"₹ "#,##0.00_-',
        ];
    }
}
