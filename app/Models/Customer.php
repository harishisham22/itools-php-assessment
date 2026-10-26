<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('customers')]
#[Table(key: 'id')]
class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name'
    ];

    protected $guarded = [
        'id'
    ];

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class);
    }
}
