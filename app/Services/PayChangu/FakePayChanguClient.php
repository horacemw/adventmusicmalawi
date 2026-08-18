<?php

namespace App\Services\PayChangu;

use App\Services\PayChangu\Dto\InitiateResult;
use App\Services\PayChangu\Dto\VerifyResult;

/**
 * Deterministic in-memory implementation for tests and local dev.
 *
 * Enable by setting PAYCHANGU_FAKE=true in .env. Initiate returns a fake checkout URL
 * that lands back on our own return URL immediately (great for E2E dev without a browser).
 * Verify auto-succeeds unless the tx_ref contains "fail" (then failed) or "pending" (then pending).
 */
class FakePayChanguClient implements PayChanguGateway
{
    public function __construct(private readonly string $webhookSecret) {}

    public function initiate(InitiateRequest $request): InitiateResult
    {
        // Fake checkout URL points back to our return endpoint with the tx_ref appended,
        // so a developer can click through the flow without leaving localhost.
        $fakeCheckout = $request->returnUrl.'?tx_ref='.$request->txRef.'&status=success';

        return InitiateResult::success($fakeCheckout, $request->txRef, [
            'status' => 'success',
            'message' => 'Fake session generated',
            'data' => [
                'event' => 'checkout.session:created',
                'checkout_url' => $fakeCheckout,
                'data' => [
                    'tx_ref' => $request->txRef,
                    'currency' => $request->currency,
                    'amount' => $request->amount,
                    'mode' => 'test',
                    'status' => 'pending',
                ],
            ],
        ]);
    }

    public function verify(string $txRef): VerifyResult
    {
        $status = match (true) {
            str_contains($txRef, 'fail') => VerifyResult::STATUS_FAILED,
            str_contains($txRef, 'pending') => VerifyResult::STATUS_PENDING,
            str_contains($txRef, 'cancel') => VerifyResult::STATUS_CANCELLED,
            default => VerifyResult::STATUS_SUCCESS,
        };

        return new VerifyResult(
            true,
            $status,
            $txRef,
            15000,
            'MWK',
            ['status' => 'success', 'data' => ['tx_ref' => $txRef, 'status' => $status, 'amount' => 15000, 'currency' => 'MWK']],
        );
    }

    public function verifyWebhookSignature(string $rawBody, ?string $signatureHeader): bool
    {
        if (!$signatureHeader || $this->webhookSecret === '') {
            return false;
        }
        return hash_equals(hash_hmac('sha256', $rawBody, $this->webhookSecret), $signatureHeader);
    }
}
