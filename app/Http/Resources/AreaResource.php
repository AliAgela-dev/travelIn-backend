<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AreaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'city_id' => $this->city_id,
            'ar_name' => $this->ar_name,
            'en_name' => $this->en_name,
            'status' => $this->status->value,
            'image'  => $this->getFirstMediaUrl('images'),
            'city'   => new CityResource($this->whenLoaded('city')),
        ];
    }
}
