<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BillExport implements WithMultipleSheets
{
    private $bill;
    private $index;

    public function __construct($bill, $index)
    {
        $this->bill = $bill;
        $this->index = $index;
    }

    public function sheets(): array
    {
        $sheets = [];

        $bill = json_decode($this->bill->data);

        $sheets[] = new InvoiceSummary($bill[$this->index]->billGroup, $bill[$this->index]->vendor);
        $sheets[] = new CoverSheet($bill[$this->index]);

        $index = 1;
        foreach($bill[$this->index]->billGroup as $b) {
            $sheets[] = new SummarySheet($b, $bill[$this->index]->vendor, $index);
            $index++;
        }
        $sheets[] = new DowntimeSheet(json_decode($this->bill->meta, true), $bill[$this->index]->vendor);

        return $sheets;
    }
}
