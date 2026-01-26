<?php

namespace App\Http\Requests\City;

use App\Enums\GeneralStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateCityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ar_name' => ['required', 'string', 'max:255', Rule::unique('cities', 'ar_name')->ignore($this->city)],
            'en_name' => ['required', 'string', 'max:255', Rule::unique('cities', 'en_name')->ignore($this->city)],
            'status' => ['required', new Enum(GeneralStatus::class)],
        ];
    }
}
