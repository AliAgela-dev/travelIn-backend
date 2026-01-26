<?php

namespace App\Http\Requests\Admin;

use App\Enums\ResortStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateResortStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ResortStatus::class)],
            'rejection_reason' => [
                Rule::requiredIf(fn () => $this->status === ResortStatus::Rejected->value),
                'string', 
                'max:1000'
            ],
        ];
    }
}
