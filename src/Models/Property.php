<?php

namespace Wardenyarn\Properties\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function values()
    {
        return $this->morphMany(PropertyValue::class, 'property');
    }
}
