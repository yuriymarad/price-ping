<?php

namespace App\Contracts;

use App\Data\OutgoingNotification;

interface NotificationSender
{
    public function send(OutgoingNotification $message): bool;
}
