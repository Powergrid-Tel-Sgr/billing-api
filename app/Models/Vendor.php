<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    public function node()
    {
        $this->hasMany(Node::class);
    }

    public function service()
    {
        $this->hasMany(Service::class);
    }
}
