<?php

namespace App\Actions\PriceDigests;

use App\Contracts\NotificationSender;
use App\Contracts\PriceDigestBuilder;

class SendPriceDigestAction
{
    public function __construct(
        private readonly NotificationSender $sender,
    ) {}

    public function handle(PriceDigestBuilder $digest): bool
    {
        $notification = $digest->build();

        if ($notification === null) {
            return false;
        }

        $this->sender->send($notification);

        return true;
    }
}
