<?php

namespace App\Http\Requests\Admin\AdminManagement;

use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isType(UserType::SuperAdmin);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['sometimes', 'string', 'max:255'],
            'phone_number' => ['sometimes', 'string', Rule::unique('users', 'phone_number')->ignore($this->admin)],
            'password' => ['nullable', 'string', 'min:8'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'date_of_birth' => ['nullable', 'date'],
            'status' => ['sometimes', 'string', 'in:active,inactive,banned'],
        ];
    }
}
