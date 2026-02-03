<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'full_name' => $this->full_name,
            'phone_number' => $this->phone_number,
            'type' => $this->type->value,
            'status' => $this->status->value,
            'avatar' => $this->getFirstMediaUrl('avatar'),
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'city' => $this->whenLoaded('city', fn() => $this->city ? [
                'id' => $this->city->id,
                'ar_name' => $this->city->ar_name,
                'en_name' => $this->city->en_name,
            ] : null),
            'created_at' => $this->created_at?->toISOString(),
            'email'=>$this->email
        ];
    }
}
