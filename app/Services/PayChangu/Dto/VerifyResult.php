<?php

namespace App\Services\PayChangu\Dto;

final class VerifyResult
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_PENDING = 'pending';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_UNKNOWN = 'unknown';

    public function __construct(
        public readonly bool $ok,
        public readonly string $status,
        public readonly ?string $txRef,
        public readonly ?int $amount,
        public readonly ?string $currency,
        /** @var array<string,mixed> */
        public readonly array $raw = [],
        public readonly ?string $error = null,
    ) {}

    public function isSuccess(): bool
    {
        return $this->ok && $this->status === self::STATUS_SUCCESS;
    }
}
