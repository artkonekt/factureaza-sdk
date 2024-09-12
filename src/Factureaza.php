<?php

declare(strict_types=1);

/**
 * Contains the Factureaza class.
 *
 * @copyright   Copyright (c) 2022 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2022-09-16
 *
 */

namespace Konekt\Factureaza;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTimeZone;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Konekt\Enum\Enum;
use Konekt\Factureaza\Contracts\Mutation;
use Konekt\Factureaza\Contracts\Query;
use Konekt\Factureaza\Contracts\Resource;
use Konekt\Factureaza\Exceptions\FactureazaException;
use Konekt\Factureaza\Exceptions\UnauthorizedException;
use Konekt\Factureaza\Models\GraphQLOperation;
use ReflectionNamedType;

final class Factureaza
{
    use Endpoints\Account;
    use Endpoints\Invoices;
    use Endpoints\Clients;

    private const SANDBOX_URL = 'https://sandbox.factureaza.ro/graphql';
    private const SANDBOX_KEY = '72543f4dc00474bc40a27916d172eb93339fae894ec7a6f2dceb4751d965';

    private string $endpoint = 'https://factureaza.ro/graphql';

    private DateTimeZone $timezone;

    private HttpClient $http;

    public function __construct(
        private string $apiKey
    ) {
        $this->timezone = new DateTimeZone('Europe/Bucharest');
        $this->http = new HttpClient();
    }

    public static function connect(string $apiKey): Factureaza
    {
        return new Factureaza($apiKey);
    }

    public static function sandbox(): Factureaza
    {
        $instance = new Factureaza(self::SANDBOX_KEY);
        $instance->endpoint = self::SANDBOX_URL;

        return $instance;
    }

    public function useUTCTime(): self
    {
        $this->timezone = new DateTimeZone('UTC');

        return $this;
    }

    public function timezone(): DateTimeZone
    {
        return $this->timezone;
    }

    protected function request(GraphQLOperation $operation, string $name, array $fields, ?array $arguments = null): Response
    {
        $query = $operation->isMutation() ? 'mutation { ' : '{ ';
        $query .= "$name ";

        if (!empty($arguments)) {
            $query .= "(\n" . $this->toGraphQLArguments($arguments) . ") ";
        }

        $query .= "{\n   " . implode("\n   ", $fields) . "\n  }\n}";

        $response = $this->http->withBasicAuth($this->apiKey, '')
            ->asJson()
            ->post($this->endpoint, ['query' => $query]);

        $this->checkForErrors($response);

        return $response;
    }

    protected function query(Query $query): Response
    {
        return $this->request(
            GraphQLOperation::QUERY(),
            $query->resource(),
            $query->fields(),
            $query->arguments(),
        );
    }

    protected function mutate(Mutation $mutation): Response
    {
        return $this->request(
            GraphQLOperation::MUTATION(),
            $mutation->operation(),
            $mutation->fields(),
            $mutation->payload(),
        );
    }

    private function remap(array $attributes, string $forClass): array
    {
        if (!in_array(Resource::class, class_implements($forClass))) {
            throw new \LogicException("The $forClass class must implement the " . Resource::class . ' interface');
        }

        $map = $forClass::attributeMap();
        $result = [];

        foreach ($attributes as $key => $value) {
            $actualKey = array_key_exists($key, $map) ? $map[$key] : $key;
            if (is_array($actualKey)) {
                $actualValue = call_user_func($actualKey[1], $value);
                $actualKey = $actualKey[0];
            } elseif ($this->isABoolProperty($actualKey, $forClass)) {
                $actualValue = 'true' === strtolower($value);
            } elseif ($this->isADateTimeProperty($actualKey, $forClass)) {
                $actualValue = $this->makeDateTime($value);
            } elseif ($enumClass = $this->isAKonektEnum($actualKey, $forClass)) {
                $actualValue = $enumClass::create($value);
            } else {
                $actualValue = $value;
            }

            $result[$actualKey] = $actualValue;
        }

        return $result;
    }

    private function isADateTimeProperty(string $property, string $class): bool
    {
        if (!property_exists($class, $property)) {
            return false;
        }

        $dateTypes = [\DateTime::class, \DateTimeImmutable::class, Carbon::class, CarbonImmutable::class];
        $details = new \ReflectionProperty($class, $property);

        if ($details->getType() instanceof ReflectionNamedType) {
            return in_array($details->getType()->getName(), $dateTypes);
        }

        return !empty(Arr::where($details->getType()->getTypes(), fn ($type) => in_array($type, $dateTypes)));
    }

    private function isAKonektEnum(string $property, string $class): false|string
    {
        if (!property_exists($class, $property)) {
            return false;
        }

        $details = new \ReflectionProperty($class, $property);

        if ($details->getType() instanceof ReflectionNamedType) {
            $types = [$details->getType()->getName()];
        } else {
            $types = $details->getType()->getTypes();
        }

        $enumTypes = Arr::where(
            $types,
            fn ($type) => class_exists($type) && in_array(Enum::class, class_parents($type))
        );

        return empty($enumTypes) ? false : $enumTypes[0];
    }

    private function isABoolProperty(string $property, string $class): bool
    {
        if (!property_exists($class, $property)) {
            return false;
        }

        $details = new \ReflectionProperty($class, $property);

        if ($details->getType() instanceof ReflectionNamedType) {
            return 'bool' === $details->getType()->getName();
        }

        return !empty(Arr::where($details->getType()->getTypes(), fn ($type) => 'bool' === $type));
    }

    private function makeDateTime(string $value): CarbonImmutable
    {
        return CarbonImmutable::parse($value)->setTimezone($this->timezone);
    }

    private function checkForErrors(Response $response): void
    {
        if (null !== $errors = $response->json('errors')) {
            throw new FactureazaException($errors[0]['message']);
        }

        if (null !== $error = $response->json('error')) {
            throw new FactureazaException($error['message']);
        }

        if (!$response->successful() && 404 !== $response->status()) {
            throw match ($response->status()) {
                401 => UnauthorizedException::make(),
                default => new FactureazaException(sprintf('Request failed with status %d. %s', $response->status(), Str::limit($response->body()))),
            };
        }
    }

    private function toGraphQLArguments(array $arguments): string
    {
        $result = '';
        foreach ($arguments as $key => $value) {
            if (is_array($value)) {
                if (Arr::isList($value)) {
                    $result .= "  $key: [\n" . $this->toGraphQLArguments($value) . "]\n";
                } else {
                    $result .= "  {\n" . $this->toGraphQLArguments($value) . "}\n";
                }
            } else {
                $result .= sprintf("  $key: %s\n", $this->escapeArgument($value));
            }
        }

        return $result;
    }

    private function escapeArgument(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? '"true"' : '"false"';
        }

        return json_encode($value);
    }
}
