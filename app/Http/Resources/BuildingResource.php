<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BuildingResource extends JsonResource
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
            'customer_name' => $this->resource->customer_name,
            'type' => $this->resource->type,
            'month' => $this->resource->month,
            'usability' => $this->resource->usability,
            'state' => $this->resource->state,
            'bill' => $this->resource->bill,
        ];
    }
}
