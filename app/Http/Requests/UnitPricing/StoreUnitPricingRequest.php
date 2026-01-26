<?php

namespace App\Http\Requests\UnitPricing;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitPricingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'price_per_night' => ['required', 'numeric', 'min:0'],
            'label' => ['nullable', 'string', 'max:255'],
        ];
    }
}
