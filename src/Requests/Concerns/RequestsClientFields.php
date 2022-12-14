<?php

declare(strict_types=1);

/**
 * Contains the RequestsClientFields trait.
 *
 * @copyright   Copyright (c) 2022 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2022-10-08
 *
 */

namespace Konekt\Factureaza\Requests\Concerns;

trait RequestsClientFields
{
    private static array $queryFields = [
        'id',
        'name',
        'isCompany',
        'registrationId',
        'taxId',
        'email',
        'uid',
        'address',
        'address2',
        'city',
        'zip',
        'state',
        'telephone',
        'country { iso }',
        'createdAt',
        'updatedAt',
    ];

    public function fields(): array
    {
        return self::$queryFields;
    }
}
