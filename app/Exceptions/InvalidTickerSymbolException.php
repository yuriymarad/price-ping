<?php

namespace App\Exceptions;

use InvalidArgumentException;

class InvalidTickerSymbolException extends InvalidArgumentException
{
    public function __construct(public readonly string $symbol, string $message)
    {
        parent::__construct($message);
    }

    public static function empty(): self
    {
        return new self('', 'Ticker symbol cannot be empty.');
    }

    public static function invalidFormat(string $symbol): self
    {
        return new self($symbol, "Invalid ticker symbol: {$symbol}");
    }
}
