<?php

namespace SurjoWallet;

class Signature
{
    public static function generate(
        string $storeId,
        string $tranId,
        string $amount,
        string $secretKey
    ): string {

        $rawString = $storeId . '|' . $tranId . '|' . $amount;

        return hash_hmac(
            'sha256',
            $rawString,
            $secretKey
        );
    }

    public static function verifyWebhook(
        string $tranId,
        string $amount,
        string $status,
        string $signature,
        string $secretKey
    ): bool {

        $rawString = $tranId . '|' . $amount . '|' . $status;

        $expected = hash_hmac(
            'sha256',
            $rawString,
            $secretKey
        );

        return hash_equals($expected, $signature);
    }
}