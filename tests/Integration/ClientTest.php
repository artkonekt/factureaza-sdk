<?php

declare(strict_types=1);

/**
 * Contains the ClientTest class.
 *
 * @copyright   Copyright (c) 2022 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2022-10-08
 *
 */

namespace Konekt\Factureaza\Tests\Integration;

use Konekt\Factureaza\Exceptions\ClientExistsException;
use Konekt\Factureaza\Factureaza;
use Konekt\Factureaza\Models\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /** @test */
    public function it_can_fetch_a_client_by_its_id()
    {
        $client = Factureaza::sandbox()->client('1064116434');

        $this->assertInstanceOf(Client::class, $client);
        $this->assertEquals('1064116434', $client->id);
        $this->assertEquals('CUBUS ARTS S.R.L.', $client->name);
        $this->assertEquals('SIBIU', $client->city);
        $this->assertEquals('Sibiu', $client->province);
        $this->assertEquals('BLD. MIHAI VITEAZU Nr. 7,Ap. 18', $client->address);
        $this->assertEquals('', $client->address2);
        $this->assertEquals('550350', $client->zip);
        $this->assertEquals('office@cubus.ro', $client->email);
        $this->assertEquals('', $client->phone);

        $this->assertTrue($client->isCompany);
        $this->assertEquals('RO', $client->country);
        $this->assertEquals('J32 /508 /2000', $client->regNo);
        $this->assertEquals('13548146', $client->taxNo);
        $this->assertEquals('RO', $client->taxNoPrefix);
        $this->assertEquals('2014-06-06 16:33:12', $client->createdAt->format('Y-m-d H:i:s'));
        // Don't test for specific data here, it might change in the future
        $this->assertGreaterThanOrEqual(
            $client->createdAt->getTimestamp(),
            $client->updatedAt->getTimestamp()
        );
    }

    /** @test */
    public function it_can_retrieve_a_client_by_tax_number()
    {
        $client = Factureaza::sandbox()->clientByTaxNo('13548146');

        $this->assertInstanceOf(Client::class, $client);
        $this->assertEquals('1064116434', $client->id);
        $this->assertEquals('13548146', $client->taxNo);
    }

    /** @test */
    public function it_can_retrieve_a_client_by_email()
    {
        $client = Factureaza::sandbox()->clientByEmail('office@cubus.ro');

        $this->assertInstanceOf(Client::class, $client);
        $this->assertEquals('1064116434', $client->id);
        $this->assertEquals('13548146', $client->taxNo);
    }

    /** @test */
    public function it_can_retrieve_a_client_by_name()
    {
        $client = Factureaza::sandbox()->clientByName('CUBUS ARTS S.R.L.');

        $this->assertInstanceOf(Client::class, $client);
        $this->assertEquals('1064116434', $client->id);
        $this->assertEquals('13548146', $client->taxNo);
    }

    /** @test */
    public function it_can_create_a_client_with_minimal_data()
    {
        $client = Factureaza::sandbox()->createClient([
            'name' => 'Giovanni Gatto',
            'isCompany' => false,
            'city' => 'Pokyo',
            'address' => 'Mishiaza Vue 72',
        ]);

        $this->assertInstanceOf(Client::class, $client);
        $this->assertNotNull($client->id);
        $this->assertEquals('Giovanni Gatto', $client->name);
        $this->assertEquals('Pokyo', $client->city);
        $this->assertEquals('Mishiaza Vue 72', $client->address);
        $this->assertFalse($client->isCompany);
        $this->assertEquals('RO', $client->country);
    }

    /** @test */
    public function attempting_to_create_a_client_with_an_existing_tax_number_throws_duplicate_client_exception()
    {
        $this->expectException(ClientExistsException::class);

        Factureaza::sandbox()->createClient([
            'name' => 'CUBUS ARTS S.R.L.',
            'isCompany' => true,
            'taxNo' => '13548146',
            'city' => 'SIBIU',
            'address' => 'BLD. MIHAI VITEAZU Nr. 7,Ap. 18',
        ]);
    }
}
