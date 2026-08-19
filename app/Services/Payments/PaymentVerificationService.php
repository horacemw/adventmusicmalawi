<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Setting;
use App\Models\Submission;
use App\Notifications\PaymentSuccessful;
use App\Notifications\SubmissionReceived;
use App\Services\PayChangu\PayChanguGateway;
use App\Services\Submissions\SubmissionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentVerificationService
{
    public function __construct(
        private readonly PayChanguGateway $gateway,
        private readonly SubmissionService $submissions,
    ) {}

    /**
     * Verify a payment server-to-server against PayChangu and advance the payment/submission state.
     * Idempotent: safe to call multiple times per tx_ref.
     */
    public function verifyAndProgress(string $txRef, string $eventType = PaymentTransaction::EVENT_VERIFY, ?string $ip = null): Payment
    {
        /** @var Payment|null $payment */
        $payment = Payment::where('provider_reference', $txRef)->first();
        if (!$payment) {
            throw new \RuntimeException("Unknown tx_ref: {$txRef}");
        }

        $result = $this->gateway->verify($txRef);

        PaymentTransaction::create([
            'payment_id' => $payment->id,
            'event_type' => $eventType,
            'status' => $result->status,
            'payload' => $result->raw,
            'ip_address' => $ip,
        ]);

        if ($payment->status === Payment::STATUS_SUCCESSFUL) {
            return $payment; // idempotent short-circuit
        }

        if (!$result->isSuccess()) {
            $payment->update([
                'status' => match ($result->status) {
                    'failed' => Payment::STATUS_FAILED,
                    'cancelled' => Payment::STATUS_CANCELLED,
                    default => Payment::STATUS_PENDING,
                },
                'failed_at' => in_array($result->status, ['failed', 'cancelled'], true) ? now() : null,
                'failure_reason' => $result->error,
            ]);

            if ($payment->payable instanceof Submission) {
                $this->submissions->markPaymentFailed($payment->payable);
            }
            return $payment->refresh();
        }

        // Amount + currency verification against expected fee (per submission kind)
        $expectedAmount = $payment->payable instanceof Submission
            ? PaymentInitiationService::feeFor($payment->payable->kind ?: Submission::KIND_SONG)
            : (int) $payment->amount;
        $expectedCurrency = (string) (Setting::get('submissions.fee_currency') ?? config('services.submissions.fee_currency', 'MWK'));

        if ($result->amount === null || $result->amount < $expectedAmount) {
            Log::warning('PayChangu verify: amount mismatch', ['tx_ref' => $txRef, 'got' => $result->amount, 'expected' => $expectedAmount]);
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'failed_at' => now(),
                'failure_reason' => 'amount_mismatch',
            ]);
            if ($payment->payable instanceof Submission) {
                $this->submissions->markPaymentFailed($payment->payable);
            }
            return $payment->refresh();
        }

        if ($result->currency && strtoupper($result->currency) !== strtoupper($expectedCurrency)) {
            Log::warning('PayChangu verify: currency mismatch', ['tx_ref' => $txRef, 'got' => $result->currency, 'expected' => $expectedCurrency]);
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'failed_at' => now(),
                'failure_reason' => 'currency_mismatch',
            ]);
            if ($payment->payable instanceof Submission) {
                $this->submissions->markPaymentFailed($payment->payable);
            }
            return $payment->refresh();
        }

        DB::transaction(function () use ($payment) {
            $payment->update([
                'status' => Payment::STATUS_SUCCESSFUL,
                'completed_at' => now(),
                'failed_at' => null,
                'failure_reason' => null,
            ]);
            if ($payment->payable instanceof Submission) {
                $this->submissions->markPaid($payment->payable);
            }
        });

        // Fire notifications once per successful transition
        $payment->refresh();
        if ($payment->payable instanceof Submission) {
            $submission = $payment->payable;
            $submission->user->notify(new PaymentSuccessful($payment));
            $submission->user->notify(new SubmissionReceived($submission));
        }

        return $payment;
    }
}
