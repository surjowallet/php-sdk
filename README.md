# Surjo Wallet PHP SDK

Official PHP SDK for Surjo Wallet Payment Gateway.

## Installation

```bash
composer require surjowallet/php-sdk
```

## Usage

```php
use SurjoWallet\Client;

$client = new Client([
    'store_id' => 'STORE_ID',
    'api_key' => 'API_KEY',
    'secret_key' => 'SECRET_KEY',
]);

$response = $client->createPayment([
    'amount' => 100,
    'tran_id' => 'INV001',
]);
```