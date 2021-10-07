<?php

namespace Wardenyarn\Properties\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyValue extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function entity($entity)
    {
        return $this->morphTo(get_class($entity), 'entity_type', 'entity_id');
    }

    public function prop($prop)
    {
        return $this->morphTo(get_class($prop), 'property_type', 'property_id');
    }
}
