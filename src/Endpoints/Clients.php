<?php

declare(strict_types=1);

/**
 * Contains the Clients trait.
 *
 * @copyright   Copyright (c) 2022 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2022-10-08
 *
 */

namespace Konekt\Factureaza\Endpoints;

use Konekt\Factureaza\Exceptions\ClientExistsException;
use Konekt\Factureaza\Exceptions\FactureazaException;
use Konekt\Factureaza\Models\Client;
use Konekt\Factureaza\Requests\CreateClient;
use Konekt\Factureaza\Requests\GetClient;
use Konekt\Factureaza\Requests\GetClientByEmail;
use Konekt\Factureaza\Requests\GetClientByName;
use Konekt\Factureaza\Requests\GetClientByTaxNo;

trait Clients
{
    public function client(string $id): ?Client
    {
        $response = $this->query(new GetClient($id));
        $data = $response->json('data')['clients'][0] ?? null;

        return is_null($data) ? null : new Client($this->remap($data, Client::class));
    }

    public function clientByTaxNo(string $taxNo): ?Client
    {
        $response = $this->query(new GetClientByTaxNo($taxNo));
        $data = $response->json('data')['clients'][0] ?? null;

        return is_null($data) ? null : new Client($this->remap($data, Client::class));
    }

    public function clientByEmail(string $email): ?Client
    {
        $response = $this->query(new GetClientByEmail($email));
        $data = $response->json('data')['clients'][0] ?? null;

        return is_null($data) ? null : new Client($this->remap($data, Client::class));
    }

    public function clientByName(string $name): ?Client
    {
        $response = $this->query(new GetClientByName($name));
        $data = $response->json('data')['clients'][0] ?? null;

        return is_null($data) ? null : new Client($this->remap($data, Client::class));
    }

    public function createClient(array|CreateClient $client): Client
    {
        $request = is_array($client) ? CreateClient::fromArray($client) : $client;
        try {
            $response = $this->mutate($request);
        } catch (FactureazaException $e) {
            if (1 === preg_match('/(CIF).*(exist).*(client)/i', $e->getMessage())) {
                throw new ClientExistsException($e->getMessage());
            }

            throw $e;
        }

        return new Client(
            $this->remap($response->json('data')['createClient'], Client::class)
        );
    }
}
