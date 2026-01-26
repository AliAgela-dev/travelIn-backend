<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'resort_id' => $this->resort_id,
            'ar_name' => $this->ar_name,
            'en_name' => $this->en_name,
            'ar_description' => $this->ar_description,
            'en_description' => $this->en_description,
            'price_per_night' => $this->price_per_night,
            'capacity' => $this->capacity,
            'room_count' => $this->room_count,
            'features' => $this->features,
            'status' => $this->status,
            'images' => $this->getMedia('images')->map(fn ($media) => [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'mime_type' => $media->mime_type,
            ]),
            'resort' => new ResortResource($this->whenLoaded('resort')),
            'reviews_avg_rating' => $this->whenNotNull($this->reviews_avg_rating),
            'reviews'=>ReviewResource::collection($this->whenLoaded('reviews')),
            'is_favorited' => auth()->check() ? auth()->user()->hasFavorited($this->resource) : false,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
