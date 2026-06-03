<?php

namespace App\Values;

use App\Exceptions\InvalidTickerSymbolException;

readonly class TickerSymbol
{
    public string $value;

    public function __construct(string $symbol)
    {
        $value = $symbol |> trim(...) |> strtoupper(...);

        if ($value === '') {
            throw InvalidTickerSymbolException::empty();
        }

        if (! preg_match('/^[A-Z0-9.\-]+$/', $value)) {
            throw InvalidTickerSymbolException::invalidFormat($symbol);
        }

        $this->value = $value;
    }
}
