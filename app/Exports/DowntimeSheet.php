<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DowntimeSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithColumnWidths, WithStrictNullComparison, WithStyles
{
    private $bill;
    private $vendor;
    private $rowCount;

    public function __construct($bill, $vendor)
    {
        $this->bill = $bill;
        $this->vendor = $vendor;
    }

    protected function createDowntimeArray($billArr)
    {
        $downtimeBillArray = [];
        $vendorBillArray = array_filter($billArr, fn ($it) => $it["service"]["vendor"]["id"] == $this->vendor->id);

        foreach ($vendorBillArray as $billItem) {

            $itemIndex = -1;
            foreach ($downtimeBillArray as $index => $downtimeItem) {
                if ($billItem["service"]["id"] == $downtimeItem["id"]) {
                    $itemIndex = $index;
                    break;
                }
            }

            if ($itemIndex > -1) {
                if (count($billItem["downtimes"]) > 0) {
                    array_push($downtimeBillArray[$itemIndex]["downtimes"], ...$billItem["downtimes"]);
                }
            } else {
                $downtimeBillArray[] = [
                    "id" => $billItem["service"]["id"],
                    "name" => $billItem["service"]["name"],
                    "downtimes" => $billItem["downtimes"]
                ];
            }
        }

        return $downtimeBillArray;
    }

    function romanize($number)
    {
        $map = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
        $returnValue = '';
        while ($number > 0) {
            foreach ($map as $roman => $int) {
                if ($number >= $int) {
                    $number -= $int;
                    $returnValue .= $roman;
                    break;
                }
            }
        }
        return $returnValue;
    }

    public function headings(): array
    {
        return [
            [
                "Downtime incurred by " . $this->vendor->name,
                "",
                "",
                "",
                "",
                "",
            ],
            [
                "S No.",
                "Customer Name",
                "",
                "Outage started At",
                "Outage resolved At",
                "Downtime",
            ],
        ];
    }

    public function collection()
    {
        $downTimeArray = $this->createDowntimeArray($this->bill);

        $row = [];
        for ($i = 0; $i < count($downTimeArray); $i++) {
            $row[] = [$i + 1, $downTimeArray[$i]["name"], "", "", "", ""];
            $totalHrs = 0;
            for ($j = 0; $j < count($downTimeArray[$i]["downtimes"]); $j++) {
                $hrs = round((($downTimeArray[$i]["downtimes"][$j]["downtime"]) / 1000 / 60 / 60), 2);
                $totalHrs += $hrs;
                $row[] = [
                    "",
                    "",
                    strtolower($this->romanize($j + 1)) . ".",
                    Carbon::createFromTimestamp(($downTimeArray[$i]["downtimes"][$j]["startedAt"]) / 1000)->format('d-m-y h:m'),
                    Carbon::createFromTimestamp(($downTimeArray[$i]["downtimes"][$j]["resolvedAt"]) / 1000)->format('d-m-y h:m'),
                    $hrs . " Hours",
                ];
            }
            $row[] = ["", "", "", "", "Total", $totalHrs . " Hours"];
        }

        $this->rowCount = count($row) + 2;
        return collect($row);
    }

    public function title(): string
    {
        return "Downtimes";
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

        $sheet->getStyle("A1:F{$this->rowCount}")->applyFromArray($styleArray);
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A:B')->getFont()->setBold(true);
        $sheet->getStyle('1')->getFont()->setSize(14);
        $sheet->getStyle('2')->getFont()->setBold(true);
        $sheet->getStyle('C:F')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    }

    public function columnWidths(): array
    {
        return [
            'C' => 5,
        ];
    }
}
