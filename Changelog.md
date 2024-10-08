# Factureaza PHP SDK Changelog

## 1.6.0
##### 2024-09-12

- Fixed possible type errors when EU id or address2 is null in the account
- Changed the API handler so that apart from 404, an exception is thrown
- Added the throwing of `UnauthorizedException` on 401 API responses

## 1.5.0
##### 2024-01-25

- Added Laravel 11 support
- Added PHP 8.3 support
- Added `clientByName()` and `clientByEmail()` methods (Thanks [@ionutcalara](https://github.com/ionutcalara)!)
- Fixed failing tests with 2024 Sandbox data

## 1.4.0
##### 2022-10-12

- Added state (`draft`, `open`*, `closed`, `cancelled`) to Invoices
- Changed the default state of created invoices from draft to open
- Added feature to specify the state of the invoice on creation

## 1.3.0
##### 2022-10-12

- Added retrieve invoice PDF method
- Added retrieve single invoice by id method

## 1.2.0
##### 2022-10-11

- Added number, total, currency and hashcode fields to invoices

## 1.1.1
##### 2022-10-11

- Fixed errors due to uninitialized Invoice annotations

## 1.1.0
##### 2022-10-11

- Added lower and upper annotation support to Invoices
- Fixed `createInvoice::withClient` method when using with a Client model

## 1.0.1
##### 2022-10-11

- Fixed false positive hydration when the API returns no results

## 1.0.0
##### 2022-10-10

- Initial release
- Sandbox or productions accounts can be used
- My Account can be queried
- Invoices can be created
- Clients can be retrieved by id and tax no
- Clients can be created
