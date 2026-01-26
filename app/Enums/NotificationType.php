<?php

namespace App\Enums;

enum NotificationType: string
{
    case BookingConfirmed = 'booking_confirmed';
    case BookingRejected = 'booking_rejected';
    case BookingCancelled = 'booking_cancelled';
    case ReviewReplied = 'review_replied';
    case SpecialOffer = 'special_offer';
    case Reminder = 'reminder';
    case Welcome = 'welcome';
    case AdminBroadcast = 'admin_broadcast';
}
