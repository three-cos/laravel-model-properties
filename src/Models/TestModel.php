<?php

namespace Wardenyarn\Properties\Models;

use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    use HasProperties;

    protected $fillable = ['name'];

    protected $properties = [
        'nickname' => [
            'cast' => 'string',
        ],
        'homepage' => [
            'cast' => 'string',
            'default' => 'www.example.site',
        ],
    ];
}