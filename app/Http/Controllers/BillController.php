<?php

namespace App\Http\Controllers;

use App\Exports\BillExport;
use App\Http\Resources\BillResource;
use App\Models\Bill;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BillController extends Controller
{
    public function index()
    {
        return ['bills' => BillResource::collection(Bill::latest()->get())];
    }

    public function store(Request $request)
    {
        $bill = new Bill();
        $bill->data = json_encode($request->bill['billEntity']);
        $bill->meta = json_encode($request->bill['billJson']);
        $bill->save();

        return ['bill' => new BillResource($bill)];
    }

    public function export(Bill $bill, $index)
    {
        return Excel::download(new BillExport($bill, $index), 'bill.xlsx');
    }
}
