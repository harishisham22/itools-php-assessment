<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('tariffs')]
#[Table(key: 'id')]
class Tariff extends Model
{
    protected $fillable = [
        'building_type',
        'category',
        'min_kwh',
        'max_kwh',
        'rate'
    ];

    protected $guarded = [
        'id'
    ];
}
