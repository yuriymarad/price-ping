<?php

namespace App\Exceptions;

use RuntimeException;

class PortfolioImportException extends RuntimeException
{
    public static function unreadableFile(): self
    {
        return new self('Unable to read the uploaded portfolio file.');
    }
}
