<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send an SMS message to the given phone number.
     */
    public function send(string $phoneNumber, string $message): bool
    {
        // In development, we just log the message
        // In production, integrate with SMS provider (Twilio, Vonage, etc.)
        
        if (app()->environment('local', 'testing')) {
            Log::channel('single')->info('SMS sent', [
                'phone_number' => $phoneNumber,
                'message' => $message,
            ]);
            
            return true;
        }

        // TODO: Implement SMS provider integration
        // Example with Twilio:
        // $this->twilioClient->messages->create($phoneNumber, [
        //     'from' => config('services.twilio.from'),
        //     'body' => $message,
        // ]);

        return $this->sendViaSmsProvider($phoneNumber, $message);
    }

    /**
     * Send an OTP code via SMS.
     */
    public function sendOtp(string $phoneNumber, string $otpCode): bool
    {
        $message = "Your Travel-In verification code is: {$otpCode}. Valid for 5 minutes.";
        
        return $this->send($phoneNumber, $message);
    }

    /**
     * Send SMS via the configured provider.
     * Override this method when implementing SMS provider integration.
     */
    protected function sendViaSmsProvider(string $phoneNumber, string $message): bool
    {
        // Placeholder for SMS provider integration
        Log::warning('SMS provider not configured', [
            'phone_number' => $phoneNumber,
            'message' => $message,
        ]);

        return false;
    }
}
