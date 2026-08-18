<?php

namespace App\Services\PayChangu;

use App\Services\PayChangu\Dto\InitiateResult;
use App\Services\PayChangu\Dto\VerifyResult;

/**
 * Abstraction over a PayChangu-like payment gateway.
 *
 * Concrete implementations: PayChanguClient (live HTTP) and FakePayChanguClient (tests / local dev).
 * Bound in AppServiceProvider so it can be swapped without touching callers.
 */
interface PayChanguGateway
{
    public function initiate(InitiateRequest $request): InitiateResult;

    public function verify(string $txRef): VerifyResult;

    /**
     * Constant-time verification of an incoming webhook signature against the raw request body.
     */
    public function verifyWebhookSignature(string $rawBody, ?string $signatureHeader): bool;
}
