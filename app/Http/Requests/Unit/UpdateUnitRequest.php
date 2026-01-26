<?php

namespace App\Http\Requests\Unit;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ar_name' => ['sometimes', 'string', 'max:255'],
            'en_name' => ['sometimes', 'string', 'max:255'],
            'ar_description' => ['nullable', 'string'],
            'en_description' => ['nullable', 'string'],
            'price_per_night' => ['sometimes', 'numeric', 'min:0'],
            'capacity' => ['sometimes', 'integer', 'min:1'],
            'room_count' => ['sometimes', 'integer', 'min:1'],
            'features' => ['nullable', 'array'],
            'media_ids' => ['nullable', 'array'],
            'media_ids.*' => ['exists:temporary_uploads,id'],
        ];
    }
}
