<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'favoritable_type' => class_basename($this->favoritable_type),
            'favoritable_id' => $this->favoritable_id,
            'favoritable' => $this->whenLoaded('favoritable'),
            'created_at' => $this->created_at,
        ];
    }
}
