<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array{label: string, bill: float, buildings: int, usability: float} $row */
        $row = $this->resource;

        return [
            'label' => $row['label'],
            'value' => $row['bill'],
            'buildings' => $row['buildings'],
            'usability' => $row['usability'],
        ];
    }
}
