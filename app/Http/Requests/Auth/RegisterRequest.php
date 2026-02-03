<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'phone_number' => ['required', 'string', 'regex:/^2189[0-9]{8}$/', 'unique:users,phone_number'],
            'otp_code' => ['required', 'string', 'size:6'],
            'full_name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'email'=>['email']
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone_number.regex' => 'Phone number must start with 2189 and be exactly 12 digits.',
            'phone_number.unique' => 'This phone number is already registered.',
            'otp_code.size' => 'OTP code must be exactly 6 digits.',
        ];
    }
}
