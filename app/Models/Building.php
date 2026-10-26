<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('buildings')]
#[Table(key: 'id')]
class Building extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'customer_uuid',
        'type',
        'month',
        'usability',
        'state',
        'bill'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
