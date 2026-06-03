<?php

namespace App\Contracts;

use App\Data\OutgoingNotification;

interface PriceDigestBuilder
{
    public function build(): ?OutgoingNotification;
}
