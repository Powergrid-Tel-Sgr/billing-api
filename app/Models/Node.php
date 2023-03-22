<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    use HasFactory;

    protected $fillable = ['label', 'region', 'vendor_id'];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function service()
    {
        return $this->hasMany(Service::class);
    }

    public static function make($label, $region, $vendor, $cps, $connectedLinks, $alias)
    {
        $node = new Node;
        $node->label = $label;
        $node->region = $region;
        $node->vendor_id = $vendor;
        $node->data = json_encode([
            'cps' => $cps ?? [],
            'connectedLinks' => $connectedLinks ?? [],
            'alias' => $alias ?? []
        ]);

        $node->save();

        return $node;
    }
}
