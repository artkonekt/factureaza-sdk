<?php

declare(strict_types=1);

/**
 * Contains the SuperTinyArrayValidatorTest class.
 *
 * @copyright   Copyright (c) 2022 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2022-10-06
 *
 */

namespace Konekt\Factureaza\Tests;

use Konekt\Factureaza\Exceptions\InvalidInvoiceItemException;
use Konekt\Factureaza\Exceptions\ValidationException;
use Konekt\Factureaza\Validation\SuperTinyArrayValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SuperTinyArrayValidatorTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(
            SuperTinyArrayValidator::class,
            SuperTinyArrayValidator::createFor('invoice item'),
        );
    }

    #[Test]
    public function it_can_validate_a_mandatory_string()
    {
        $validator = SuperTinyArrayValidator::createFor('invoice item');
        $schema = ['name' => 'string*'];
        $data = ['name' => 'Bud Spencer'];

        $validated = $validator->validate($schema, $data);
        $this->assertIsArray($validated);
        $this->assertEquals($data, $validated);
    }

    #[Test]
    public function it_can_validate_an_optional_string()
    {
        $validator = SuperTinyArrayValidator::createFor('invoice item');
        $schema = ['name' => 'string'];
        $data = ['name' => 'Bud Spencer'];

        $validated = $validator->validate($schema, $data);
        $this->assertIsArray($validated);
        $this->assertEquals($data, $validated);
    }

    #[Test]
    public function it_allows_null_for_an_optional_string()
    {
        $validator = SuperTinyArrayValidator::createFor('invoice item');
        $schema = ['name' => 'string'];
        $data = ['name' => null];

        $this->assertEquals($data, $validator->validate($schema, $data));
    }

    #[Test]
    public function it_does_not_allow_integer_for_an_optional_string()
    {
        $validator = SuperTinyArrayValidator::createFor('invoice item');

        $this->expectException(ValidationException::class);
        $validator->validate(['name' => 'string'], ['name' => 1]);
    }

    #[Test]
    public function the_default_value_for_an_optional_field_can_be_set()
    {
        $validator = SuperTinyArrayValidator::createFor('invoice item');

        $validated = $validator->validate(['quantity' => 'string:default=1'], []);
        $this->assertEquals('1', $validated['quantity']);
    }

    #[Test]
    public function it_can_validate_a_mandatory_number()
    {
        $validator = SuperTinyArrayValidator::createFor('invoice item');
        $schema = ['price' => 'number*'];
        $data = ['price' => 35.90];

        $validated = $validator->validate($schema, $data);
        $this->assertIsArray($validated);
        $this->assertEquals($data, $validated);
    }

    #[Test]
    public function it_does_not_allow_null_for_a_mandatory_number()
    {
        $validator = SuperTinyArrayValidator::createFor('invoice item');

        $this->expectException(ValidationException::class);

        $validator->validate(['price' => 'number*'], ['price' => null]);
    }

    #[Test]
    public function it_can_validate_an_optional_number()
    {
        $validator = SuperTinyArrayValidator::createFor('invoice item');
        $schema = ['price' => 'number'];
        $data = ['price' => 12];

        $validated = $validator->validate($schema, $data);
        $this->assertIsArray($validated);
        $this->assertEquals($data, $validated);
    }

    #[Test]
    public function it_allows_null_for_an_optional_number()
    {
        $validator = SuperTinyArrayValidator::createFor('invoice item');
        $schema = ['qty' => 'number'];
        $data = ['qty' => null];

        $this->assertEquals($data, $validator->validate($schema, $data));
    }

    #[Test]
    public function a_default_for_numbers_can_be_set()
    {
        $validator = SuperTinyArrayValidator::createFor('invoice item');
        $schema = ['qty' => 'number:default=1'];
        $data = ['qty' => null];

        $this->assertEquals(['qty' => 1], $validator->validate($schema, $data));
    }

    #[Test]
    public function it_can_validate_an_optional_bool()
    {
        $validator = SuperTinyArrayValidator::createFor('client');
        $schema = ['isCompany' => 'bool'];
        $data = ['isCompany' => true];

        $validated = $validator->validate($schema, $data);
        $this->assertIsArray($validated);
        $this->assertEquals($data, $validated);
    }

    #[Test]
    public function it_can_validate_a_mandatory_bool()
    {
        $validator = SuperTinyArrayValidator::createFor('client');
        $schema = ['isCompany' => 'bool*'];
        $data = ['isCompany' => true];

        $validated = $validator->validate($schema, $data);
        $this->assertIsArray($validated);
        $this->assertEquals($data, $validated);
    }

    #[Test]
    public function it_allows_0_as_boolean_false()
    {
        $validator = SuperTinyArrayValidator::createFor('client');
        $schema = ['isCompany' => 'bool*'];
        $data = ['isCompany' => 0];

        $this->assertFalse($validator->validate($schema, $data)['isCompany']);
    }

    #[Test]
    public function it_allows_1_as_boolean_true()
    {
        $validator = SuperTinyArrayValidator::createFor('client');
        $schema = ['isCompany' => 'bool*'];
        $data = ['isCompany' => 1];

        $this->assertTrue($validator->validate($schema, $data)['isCompany']);
    }

    #[Test]
    public function it_throws_a_validation_exception_if_a_mandatory_bool_is_null()
    {
        $validator = SuperTinyArrayValidator::createFor('invoice');
        $schema = ['isReverseVat' => 'bool*'];
        $data = ['isReverseVat' => null];

        $this->expectException(ValidationException::class);
        $validator->validate($schema, $data);
    }

    #[Test]
    public function it_throws_a_validation_exception_if_a_mandatory_field_is_missing()
    {
        $validator = SuperTinyArrayValidator::createFor('invoice');
        $schema = ['number' => 'string*'];
        $data = [];

        $this->expectException(ValidationException::class);
        $validator->validate($schema, $data);
    }

    #[Test]
    public function it_throws_a_validation_exception_if_a_mandatory_field_is_null()
    {
        $validator = SuperTinyArrayValidator::createFor('invoice');
        $schema = ['number' => 'string*'];
        $data = ['number' => null];

        $this->expectException(ValidationException::class);
        $validator->validate($schema, $data);
    }

    #[Test]
    public function the_exception_message_tells_that_the_value_is_null()
    {
        $validator = SuperTinyArrayValidator::createFor('customer');

        $this->expectExceptionMessage('The customer `name` field value (`NULL`) is invalid');
        $validator->validate(['name' => 'string*'], ['name' => null]);
    }

    #[Test]
    public function the_exception_type_can_be_specified()
    {
        $validator = SuperTinyArrayValidator::createFor('invoice item')
            ->onErrorThrow(InvalidInvoiceItemException::class);

        $this->expectException(InvalidInvoiceItemException::class);
        $validator->validate(['description' => 'string*'], []);
    }
}
