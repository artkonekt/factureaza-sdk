# PHP 8.1 SDK for factureaza.ro API

[![Tests](https://img.shields.io/github/actions/workflow/status/artkonekt/factureaza-sdk/tests.yml?branch=master&style=flat-square)](https://github.com/artkonekt/factureaza-sdk/actions?query=workflow%3Atests)
[![Packagist version](https://img.shields.io/packagist/v/konekt/factureaza-sdk.svg?style=flat-square)](https://packagist.org/packages/konekt/factureaza-sdk)
[![Packagist downloads](https://img.shields.io/packagist/dt/konekt/factureaza-sdk.svg?style=flat-square)](https://packagist.org/packages/konekt/factureaza-sdk)
[![StyleCI](https://styleci.io/repos/537435324/shield?branch=master)](https://styleci.io/repos/537435324)
[![MIT Software License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE.md)

This package provides a PHP SDK for interacting with the [factureaza.ro GraphQL API](https://factureaza.ro/documentatie-api).

## Installation

> The minimum requirement of this package is PHP 8.1.

To install this library in your application, use composer:

```bash
composer require konekt/factureaza-sdk
```

## Usage

### Live or Sandbox

To connect to the live system, use the `connect` method and pass your api key:

```php
$live = Factureaza::connect('api key here');
$live->myAccount();
// => Konekt\Factureaza\Models\MyAccount
//     id: "555000444",
//     name: "yourcompany",
//     companyName: "Your Company SRL",
//     createdAt: "2019-06-06T16:23:34+03:00",
//     updatedAt: "2022-09-13T08:03:29+03:00"
//     ...
```

To connect to the sandbox system, use the `sandbox` method:

```php
$sandbox = Factureaza::sandbox();
$sandbox->myAccount();
// => Konekt\Factureaza\Models\MyAccount
//     id: "340138083",
//     name: "sandbox",
//     companyName: "Test Services SRL",
//     createdAt: "2014-06-06T16:23:34+03:00",
//     updatedAt: "2022-09-13T08:03:29+03:00"
//     ...
```

### Time Zone

The factureaza.ro API returns dates in the Romanian time zone (Europe/Bucharest).
This SDK returns dates in that timezone by default.

If you want dates to be returned in UTC, call the `useUTCTime()` method:

```php
$factureaza = Factureaza::connect('api key');
$factureaza->myAccount()->createdAt->toIso8601String();
// 2014-06-06T16:23:34+03:00

$factureaza->useUtcTime();
$factureaza->myAccount()->createdAt->toIsoString();
// 2014-06-06T13:23:34+00:00
```

### Create an Invoice

```php
$request = CreateInvoice::inSeries('1061104148')
    ->forClient('1064116434')
    ->withEmissionDate('2021-09-17')
    ->addItem(['description' => 'Service', 'price' => 19, 'unit' => 'luna', 'productCode' => '']);

$invoice = Factureaza::sandbox()->createInvoice($request);
//=> Konekt\Factureaza\Models\Invoice {#2760
//     +documentDate: Carbon\CarbonImmutable @1631826000 {#2773
//       date: 2021-09-17 00:00:00.0 Europe/Bucharest (+03:00),
//     },
//     +clientId: "1064116434",
//     +items: [
//       Konekt\Factureaza\Models\InvoiceItem {#2765
//         +description: "Service",
//         +price: 19.0,
//         +unit: "luna",
//         +quantity: 1.0,
//         +productCode: "",
//         +id: "1056077322",
//       },
//     ],
//     +id: "1065254080",
//     +createdAt: Carbon\CarbonImmutable @1665076996 {#2772
//       date: 2022-10-06 20:23:16.0 Europe/Bucharest (+03:00),
//     },
//     +updatedAt: Carbon\CarbonImmutable @1665076996 {#2771
//       date: 2022-10-06 20:23:16.0 Europe/Bucharest (+03:00),
//     },
//   }
```

### Document States

Invoices and other documents can have 4 states: `draft`, `open`, `closed`, `cancelled`.

When creating an Invoice, it will have the `open` state by default.

If you want to create an invoice with a different initial state, use one of the following
methods on the `CreateInvoice` class:

- `asDraft()`
- `asClosed()`
- `asCancelled()`

```php
$request = CreateInvoice::inSeries('1061104148')->asDraft();
//...
Factureaza::sandbox()->createInvoice($request);
```

or:

```php
$request = CreateInvoice::inSeries('1061104148')->asClosed();
//...
Factureaza::sandbox()->createInvoice($request);
```

### Retrieve Invoice PDF

The PDF of an Invoice can be retrieved in base64 encoded format:

```php
$invoiceId = '1234567';
$pdf = Factureaza::connect('your-api-key')->invoiceAsPdfBase64($invoiceId);

// Mind decoding it when you want to save it:

file_put_contents('invoice.pdf', base64_decode($pdf));
```

### Retrieve a Single Invoice

Unique invoices can be retrieved by id:

```php
$invoiceId = '1065254039';
$invoice = Factureaza::connect('your-api-key')->invoice($invoiceId);
//=> Konekt\Factureaza\Models\Invoice {#2760
```

### Find Clients

You can retrieve a client either by its Factureaza ID, or by tax number (cod fiscal).

#### Find a Client by Factureaza ID

```php
$client = Factureaza::sandbox()->client('1064116434');
//=> Konekt\Factureaza\Models\Client {#2691
//     +name: "CUBUS ARTS S.R.L.",
//     +isCompany: true,
//     +address: "BLD. MIHAI VITEAZU Nr. 7,Ap. 18",
//     +address2: "",
//     +zip: "550350",
//     +city: "SIBIU",
//     +province: "Sibiu",
//     +country: "RO",
//     +email: "office@cubus.ro",
//     +regNo: "J32 /508 /2000",
//     +taxNo: "13548146",
//     +taxNoPrefix: "RO",
//     +id: "1064116434",
//     +createdAt: Carbon\CarbonImmutable @1402061592 {#2708
//       date: 2014-06-06 16:33:12.0 Europe/Bucharest (+03:00),
//     },
//     +updatedAt: Carbon\CarbonImmutable @1402061592 {#2696
//       date: 2014-06-06 16:33:12.0 Europe/Bucharest (+03:00),
//     },
//   }
```

#### Find a Client by Tax Number

```php
$client = Factureaza::sandbox()->clientByTaxNo('13548146');
```

### Create a Client

```php
$client = Factureaza::sandbox()->createClient([
    'name' => 'Giovanni Gatto',
    'isCompany' => false,
    'city' => 'Pokyo',
    'address' => 'Mishiaza Vue 72',
]);
//=> Konekt\Factureaza\Models\Client {#2701
//     +name: "Giovanni Gatto",
//     +isCompany: false,
//     +address: "Mishiaza Vue 72",
//     +address2: null,
//     +zip: null,
//     +city: "Pokyo",
//     +province: null,
//     +country: "RO",
//     +email: null,
//     +phone: null,
//     +regNo: null,
//     +taxNo: "",
//     +taxNoPrefix: null,
//     +id: "1064116440",
//     +createdAt: Carbon\CarbonImmutable @1665343572 {#2692
//       date: 2022-10-09 22:26:12.0 Europe/Bucharest (+03:00),
//     },
//     +updatedAt: Carbon\CarbonImmutable @1665343572 {#2722
//       date: 2022-10-09 22:26:12.0 Europe/Bucharest (+03:00),
//     },
//   }
```

Factureaza identifies clients based on their `taxNo` (`uid` in Factureaza API) field,
which represents either the tax number (CIF/CUI) of a company or the
personal identification number (CNP) of a natural person.

If you try to create a client with a `taxNo` that already exists, a `ClientExistsException`
is thrown.

