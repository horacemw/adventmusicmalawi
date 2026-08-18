<?php

namespace App\Services\PayChangu;

final class InitiateRequest
{
    public function __construct(
        public readonly string $txRef,
        public readonly int $amount,
        public readonly string $currency,
        public readonly string $email,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $callbackUrl,
        public readonly string $returnUrl,
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        /** @var array<string,mixed> */
        public readonly array $meta = [],
    ) {}
}
