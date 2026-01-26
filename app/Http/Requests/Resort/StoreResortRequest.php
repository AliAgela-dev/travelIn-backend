<?php

namespace App\Http\Requests\Resort;

use App\Models\Resort;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreResortRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Resort owners can only have one resort
        return !$this->user()->resorts()->exists();
    }

    public function rules(): array
    {
        return [
            'city_id' => ['required', 'exists:cities,id'],
            'area_id' => ['required', 'exists:areas,id'],
            'ar_name' => ['required', 'string', 'max:255'],
            'en_name' => ['required', 'string', 'max:255'],
            'ar_description' => ['required', 'string'],
            'en_description' => ['required', 'string'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'media_ids' => ['array', 'nullable'],
            'media_ids.*' => ['exists:temporary_uploads,id'],
        ];
    }
}
