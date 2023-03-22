<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BillResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return ([
            'id' => $this->id,
            'billEntity' => json_decode($this->data, true),
            'billJson' => json_decode($this->meta, true),
            'created_at' => $this->created_at,
        ]);
    }
}
