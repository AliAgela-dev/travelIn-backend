<?php

namespace App\Http\Requests\Booking;

use App\Enums\BookingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookingStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([BookingStatus::Confirmed->value, BookingStatus::Rejected->value])],
            'owner_notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
