<?php

declare(strict_types=1);

/**
 * Contains the UnauthorizedException class.
 *
 * @copyright   Copyright (c) 2024 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2024-09-12
 *
 */

namespace Konekt\Factureaza\Exceptions;

class UnauthorizedException extends FactureazaException
{
    public static function make(): self
    {
        return new self('Factureaza returned 401. Did you pass a valid API key?');
    }
}
