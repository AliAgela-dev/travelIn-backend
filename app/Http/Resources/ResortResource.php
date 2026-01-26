<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResortResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'owner'=>new UserResource($this->whenLoaded('owner')),
            'city' => new CityResource($this->whenLoaded('city')),
            'area' => new AreaResource($this->whenLoaded('area')),
            'ar_name' => $this->ar_name,
            'en_name' => $this->en_name,
            'ar_description' => $this->ar_description,
            'en_description' => $this->en_description,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
            'rejection_reason' => $this->when($this->status === \App\Enums\ResortStatus::Rejected || $this->status === \App\Enums\ResortStatus::Rejected->value, $this->rejection_reason),
            'images' => $this->getMedia('images')->map(fn ($media) => [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'mime_type' => $media->mime_type,
            ]),
            'reviews_avg_rating' => $this->whenNotNull($this->reviews_avg_rating),
            'reviews_count' => $this->whenNotNull($this->reviews_count),
            'reviews'=>ReviewResource::collection($this->whenLoaded('reviews')),
            'is_favorited' => auth()->check() ? auth()->user()->hasFavorited($this->resource) : false,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
