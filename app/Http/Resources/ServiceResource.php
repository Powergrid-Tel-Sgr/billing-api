<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $remarks = json_decode($this->remarks, true);

        return ([
            'id' => $this->id,
            'vendor' => New VendorResource($this->vendor),
            'node' => New NodeResource($this->node),

            'name' => $this->name,
            'cpNumber' => $this->cpNumber,
            'capacity' => $this->capacity,
            'linkFrom' => $this->linkFrom,
            'linkTo' => $this->linkTo,
            'doco' => $this->doco,
            'lastMile' => $this->lastMile,
            'annualInvoiceValue' => $this->annualInvoiceValue,
            'sharePercent' => $this->sharePercent,
            'discountOffered' => $this->discountOffered,
            'annualVendorValue' => $this->annualVendorValue,
            'unitRate' => $this->unitRate,
            'totalPODays' => $this->totalPODays,
            'remarks' => $remarks,
        ]);
    }
}
