<?php

namespace App\Http\Requests\Resort;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResortRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Must be the owner. Checked by policy or controller logic.
        return true; 
    }

    public function rules(): array
    {
        return [
            'city_id' => ['sometimes', 'exists:cities,id'],
            'area_id' => ['sometimes', 'exists:areas,id'],
            'ar_name' => ['sometimes', 'string', 'max:255'],
            'en_name' => ['sometimes', 'string', 'max:255'],
            'ar_description' => ['sometimes', 'string'],
            'en_description' => ['sometimes', 'string'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'media_ids' => ['array', 'nullable'],
            'media_ids.*' => ['exists:temporary_uploads,id'],
        ];
    }
}
