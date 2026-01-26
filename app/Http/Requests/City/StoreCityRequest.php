<?php

namespace App\Http\Requests\City;

use App\Enums\GeneralStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\GenericRule;
use Illuminate\Validation\Rules\Enum;

class StoreCityRequest extends FormRequest
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
            'ar_name' => ['required', 'string', 'max:255', 'unique:cities,ar_name'],
            'en_name' => ['required', 'string', 'max:255', 'unique:cities,en_name'],
            'status' => ['required', new Enum(GeneralStatus::class)],
        ];
    }
}
