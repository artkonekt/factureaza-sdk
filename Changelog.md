# Factureaza PHP SDK Changelog

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
