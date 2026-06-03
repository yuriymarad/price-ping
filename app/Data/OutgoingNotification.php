<?php

namespace App\Data;

readonly class OutgoingNotification
{
    public function __construct(
        public string $title,
        public string $body,
    ) {}
}
