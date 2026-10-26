<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    protected $fillable = [
        'building_type',
        'category',
        'min_kwh',
        'max_kwh',
        'rate',
        'is_initial_rate',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_kwh' => 'integer',
            'max_kwh' => 'integer',
            'rate' => 'float',
            'is_initial_rate' => 'boolean',
        ];
    }

    public function validateNoOverlap(string $buildingType, ?int $min, ?int $max, ?int $ignoreId = null): bool
    {
        $query = Tariff::where('building_type', $buildingType)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where(function ($q) use ($min, $max) {
                $q->where(function ($sub) use ($min, $max) {
                    $sub->where('min_kwh', '>=', $min)
                        ->when($max, fn ($s) => $s->where('min_kwh', '<=', $max));
                })
                    ->orWhere(function ($sub) use ($min, $max) {
                        $sub->where('max_kwh', '>=', $min)
                            ->when($max, fn ($s) => $s->where('max_kwh', '<=', $max));
                    })
                    ->orWhere(function ($sub) use ($min, $max) {
                        $sub->where('min_kwh', '<=', $min)
                            ->where(function ($s) use ($max) {
                                $s->whereNull('max_kwh')
                                    ->when($max, fn ($t) => $t->orWhere('max_kwh', '>=', $max));
                            });
                    });
            });

        return ! $query->exists();
    }
}
