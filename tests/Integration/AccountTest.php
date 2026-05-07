<?php

declare(strict_types=1);

/**
 * Contains the AccountTest class.
 *
 * @copyright   Copyright (c) 2022 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2022-09-16
 *
 */

namespace Konekt\Factureaza\Tests\Integration;

use Konekt\Factureaza\Factureaza;
use Konekt\Factureaza\Models\MyAccount;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AccountTest extends TestCase
{
    #[Test]
    public function it_can_connect_to_the_sandbox_and_retrieves_all_the_account_data()
    {
        $account = Factureaza::sandbox()->myAccount();

        $this->assertInstanceOf(MyAccount::class, $account);
        $this->assertEquals('340138083', $account->id);
        $this->assertEquals('sandbox', $account->name);
        $this->assertEquals('Test Services SRL', $account->companyName);
        $this->assertEquals('Brașov', $account->city);
        $this->assertEquals('Brașov', $account->province);
        $this->assertEquals('A. Hirscher nr. 11', $account->address1);
        $this->assertEquals('', $account->address2);
        $this->assertEquals('500015', $account->zip);
        $this->assertEquals('RO', $account->country);
        $this->assertEquals('J03/44/1999', $account->regNo);
        $this->assertEquals('24335356', $account->taxNo);
        $this->assertEquals('RO', $account->taxNoPrefix);
        $this->assertEquals('', $account->euid);
        $this->assertEquals('RON', $account->domesticCurrency);
        $this->assertEquals('2014-06-06 16:23:34', $account->createdAt->format('Y-m-d H:i:s'));
        // Don't test for specific data here, it might change in the future
        $this->assertGreaterThan(
            $account->createdAt->getTimestamp(),
            $account->updatedAt->getTimestamp()
        );
    }
}
