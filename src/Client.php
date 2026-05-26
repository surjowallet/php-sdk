<?php

namespace SurjoWallet;

class Client
{
    protected string $storeId;
    protected string $apiKey;
    protected string $secretKey;
    protected string $baseUrl;

    public function __construct(array $config)
    {
        $this->storeId = $config['store_id'] ?? '';
        $this->apiKey = $config['api_key'] ?? '';
        $this->secretKey = $config['secret_key'] ?? '';
        $this->baseUrl = rtrim(
            $config['base_url'] ?? 'https://bio.linkpc.net/api',
            '/'
        );

        if (
            empty($this->storeId) ||
            empty($this->apiKey) ||
            empty($this->secretKey)
        ) {
            throw new SurjoWalletException(
                'Store ID, API Key and Secret Key are required.'
            );
        }
    }

    public function createPayment(array $data): array
    {
        if (empty($data['amount'])) {
            throw new SurjoWalletException('Amount is required.');
        }

        if (empty($data['tran_id'])) {
            throw new SurjoWalletException('Transaction ID is required.');
        }

        $amount = number_format(
            (float) $data['amount'],
            2,
            '.',
            ''
        );

        $signature = Signature::generate(
            $this->storeId,
            $data['tran_id'],
            $amount,
            $this->secretKey
        );

        $payload = [
            'store_id' => $this->storeId,
            'api_key' => $this->apiKey,
            'amount' => $amount,
            'tran_id' => $data['tran_id'],
            'currency' => $data['currency'] ?? 'BDT',
            'customer_name' => $data['customer_name'] ?? null,
            'customer_mobile' => $data['customer_mobile'] ?? null,
            'customer_email' => $data['customer_email'] ?? null,
            'success_url' => $data['success_url'] ?? '',
            'failed_url' => $data['failed_url'] ?? '',
            'cancel_url' => $data['cancel_url'] ?? '',
            'webhook_url' => $data['webhook_url'] ?? '',
            'signature' => $signature,
        ];

        return $this->request(
            '/payment/create',
            $payload
        );
    }

    public function verifyPayment(string $tranId): array
    {
        $rawString = $this->storeId . '|' . $tranId;

        $signature = hash_hmac(
            'sha256',
            $rawString,
            $this->secretKey
        );

        return $this->request('/payment/verify', [
            'store_id' => $this->storeId,
            'api_key' => $this->apiKey,
            'tran_id' => $tranId,
            'signature' => $signature,
        ]);
    }

    protected function request(
        string $endpoint,
        array $payload
    ): array {

        $url = $this->baseUrl . $endpoint;

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            throw new SurjoWalletException(
                curl_error($ch)
            );
        }

        $httpCode = curl_getinfo(
            $ch,
            CURLINFO_HTTP_CODE
        );

        curl_close($ch);

        $result = json_decode($response, true);

        if (!is_array($result)) {
            throw new SurjoWalletException(
                'Invalid API response.'
            );
        }

        $result['_http_code'] = $httpCode;

        return $result;
    }
}