<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Building extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_name',
        'type',
        'month',
        'usability',
        'state',
        'bill',
    ];

    /**
     * Scope a query to filter by building type.
     */
    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        return $query->when($type, fn ($q) => $q->where('type', $type));
    }

    /**
     * Scope a query to filter by state.
     */
    public function scopeOfState(Builder $query, ?string $state): Builder
    {
        return $query->when($state, fn ($q) => $q->where('state', $state));
    }

    /**
     * Scope a query to filter by month.
     */
    public function scopeOfMonth(Builder $query, ?int $month): Builder
    {
        return $query->when($month, fn ($q) => $q->where('month', $month));
    }

    /**
     * Scope a query to search by customer name.
     */
    public function scopeSearchCustomer(Builder $query, ?string $search): Builder
    {
        return $query->when(
            $search,
            fn ($q) => $q->where('customer_name', 'like', "%{$search}%"),
        );
    }

    /**
     * Scope a query to sort by allowed columns (type, usability).
     */
    public function scopeSortBy(Builder $query, ?string $column, string $direction = 'asc'): Builder
    {
        $allowedColumns = ['type', 'usability'];
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return $query->when(in_array($column, $allowedColumns), function ($q) use ($column, $direction) {
            $q->orderBy($column, $direction);
        });
    }
}
