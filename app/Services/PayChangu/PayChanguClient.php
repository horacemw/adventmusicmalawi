<?php

namespace App\Services\PayChangu;

use App\Services\PayChangu\Dto\InitiateResult;
use App\Services\PayChangu\Dto\VerifyResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayChanguClient implements PayChanguGateway
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $secretKey,
        private readonly string $webhookSecret,
    ) {}

    public function initiate(InitiateRequest $request): InitiateResult
    {
        try {
            $response = Http::withToken($this->secretKey)
                ->acceptJson()
                ->asJson()
                ->timeout(20)
                ->post($this->baseUrl.'/payment', [
                    'tx_ref' => $request->txRef,
                    'amount' => $request->amount,
                    'currency' => $request->currency,
                    'email' => $request->email,
                    'first_name' => $request->firstName,
                    'last_name' => $request->lastName,
                    'callback_url' => $request->callbackUrl,
                    'return_url' => $request->returnUrl,
                    'customization' => array_filter([
                        'title' => $request->title,
                        'description' => $request->description,
                    ]),
                    'meta' => $request->meta,
                ]);
        } catch (ConnectionException $e) {
            Log::error('PayChangu initiate connection failed', ['msg' => $e->getMessage()]);
            return InitiateResult::failure('connection_error');
        }

        $body = $response->json() ?? [];

        if (!$response->successful() || ($body['status'] ?? null) !== 'success') {
            Log::warning('PayChangu initiate rejected', ['status' => $response->status(), 'body' => $body]);
            return InitiateResult::failure(
                (string) ($body['message'] ?? 'initiate_failed'),
                $body,
            );
        }

        $data = $body['data'] ?? [];
        $checkoutUrl = $data['checkout_url'] ?? null;
        $returnedTxRef = $data['data']['tx_ref'] ?? $request->txRef;

        if (!$checkoutUrl) {
            return InitiateResult::failure('missing_checkout_url', $body);
        }

        return InitiateResult::success($checkoutUrl, $returnedTxRef, $body);
    }

    public function verify(string $txRef): VerifyResult
    {
        try {
            $response = Http::withToken($this->secretKey)
                ->acceptJson()
                ->timeout(20)
                ->get($this->baseUrl.'/verify-payment/'.rawurlencode($txRef));
        } catch (ConnectionException $e) {
            Log::error('PayChangu verify connection failed', ['tx_ref' => $txRef, 'msg' => $e->getMessage()]);
            return new VerifyResult(false, VerifyResult::STATUS_UNKNOWN, $txRef, null, null, [], 'connection_error');
        }

        $body = $response->json() ?? [];

        if (!$response->successful() || ($body['status'] ?? null) !== 'success') {
            return new VerifyResult(
                false,
                VerifyResult::STATUS_UNKNOWN,
                $txRef,
                null,
                null,
                $body,
                (string) ($body['message'] ?? 'verify_failed'),
            );
        }

        $data = $body['data'] ?? [];
        $status = strtolower((string) ($data['status'] ?? VerifyResult::STATUS_UNKNOWN));
        $status = in_array($status, [
            VerifyResult::STATUS_SUCCESS,
            VerifyResult::STATUS_FAILED,
            VerifyResult::STATUS_PENDING,
            VerifyResult::STATUS_CANCELLED,
        ], true) ? $status : VerifyResult::STATUS_UNKNOWN;

        return new VerifyResult(
            true,
            $status,
            (string) ($data['tx_ref'] ?? $txRef),
            isset($data['amount']) ? (int) $data['amount'] : null,
            $data['currency'] ?? null,
            $body,
        );
    }

    public function verifyWebhookSignature(string $rawBody, ?string $signatureHeader): bool
    {
        if (!$signatureHeader || $this->webhookSecret === '') {
            return false;
        }
        $expected = hash_hmac('sha256', $rawBody, $this->webhookSecret);
        return hash_equals($expected, $signatureHeader);
    }
}
