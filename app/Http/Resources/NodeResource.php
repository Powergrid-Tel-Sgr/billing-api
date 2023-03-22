<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NodeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = json_decode($this->data);

        return ([
            'id' => $this->id,
            'label' => $this->label,
            'region' => $this->region,
            'vendor' => $this->vendor_id,
            'cps' => $data->cps ?? [],
            'connectedLinks' => $data->connectedLinks ?? [],
            'alias' => $data->alias ?? [],
        ]);
    }
}
