<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TariffResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'building_type' => $this->resource->building_type,
            'category' => $this->resource->category,
            'min_kwh' => $this->resource->min_kwh,
            'max_kwh' => $this->resource->max_kwh,
            'rate' => $this->resource->rate,
            'is_initial_rate' => $this->resource->is_initial_rate,
        ];
    }
}
