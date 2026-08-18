<?php

namespace App\Services\PayChangu\Dto;

final class InitiateResult
{
    public function __construct(
        public readonly bool $ok,
        public readonly ?string $checkoutUrl,
        public readonly ?string $txRef,
        /** @var array<string,mixed> */
        public readonly array $raw = [],
        public readonly ?string $error = null,
    ) {}

    public static function success(string $checkoutUrl, string $txRef, array $raw): self
    {
        return new self(true, $checkoutUrl, $txRef, $raw, null);
    }

    public static function failure(string $error, array $raw = []): self
    {
        return new self(false, null, null, $raw, $error);
    }
}
