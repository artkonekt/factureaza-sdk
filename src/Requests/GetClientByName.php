<?php

declare(strict_types=1);

/**
 * Contains the GetClientByName class.
 *
 * @copyright   Copyright (c) 2024 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2024-01-25
 *
 */

namespace Konekt\Factureaza\Requests;

use Konekt\Factureaza\Contracts\Query;
use Konekt\Factureaza\Requests\Concerns\RequestsClientFields;

class GetClientByName implements Query
{
    use RequestsClientFields;

    public function __construct(
        private readonly string $name,
    ) {
    }

    public function resource(): string
    {
        return 'clients';
    }

    public function arguments(): ?array
    {
        return ['name' => $this->name];
    }
}
