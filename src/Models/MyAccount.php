<?php

declare(strict_types=1);

/**
 * Contains the MyAccount class.
 *
 * @copyright   Copyright (c) 2022 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2022-09-16
 *
 */

namespace Konekt\Factureaza\Models;

use Konekt\Factureaza\Contracts\Resource;

class MyAccount implements Resource
{
    use HasDynamicAttributeConstructor;
    use HasId;
    use HasTimestamps;

    public readonly string $name;

    public readonly string $companyName;

    public readonly string $address1;

    public readonly ?string $address2;

    public readonly string $zip;

    public readonly string $city;

    public readonly string $province;

    public readonly string $country;

    public readonly string $email;

    public readonly string $regNo;

    public readonly string $taxNo;

    public readonly string $taxNoPrefix;

    public readonly ?string $euid;

    public readonly string $domesticCurrency;

    public static function attributeMap(): array
    {
        return [
            'companyAddress1' => 'address1',
            'companyAddress2' => 'address2',
            'companyCity' => 'city',
            'companyCountry' => ['country', fn (array $country) => $country['iso']],
            'companyRegistrationId' => 'regNo',
            'companyUid' => 'taxNo',
            'companyTaxId' => 'taxNoPrefix',
            'companyState' => 'province',
            'companyZip' => 'zip',
            'companyEuid' => 'euid',
        ];
    }
}
