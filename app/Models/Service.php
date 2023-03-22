<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory;
    use SoftDeletes;

    public static function make($request)
    {
        $service = new Service();

        $service->vendor_id = (string)$request->vendor["id"];
        $service->node_id = (string)$request->node["id"];
        $service->name = $request->name;
        $service->cpNumber = $request->cpNumber;
        $service->capacity = $request->capacity;
        $service->linkFrom = $request->linkFrom;
        $service->linkTo = $request->linkTo;
        $service->doco = $request->doco;
        $service->lastMile = $request->lastMile;
        $service->annualInvoiceValue = $request->annualInvoiceValue;
        $service->sharePercent = $request->sharePercent;
        $service->discountOffered = $request->discountOffered;
        $service->annualVendorValue = $request->annualVendorValue;
        $service->unitRate = $request->unitRate;
        $service->totalPODays = $request->totalPODays;

        $service->save();

        return $service;
    }


    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function node()
    {
        return $this->belongsTo(Node::class);
    }
}
