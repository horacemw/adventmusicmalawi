<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Setting;
use App\Models\Submission;
use App\Models\User;
use App\Services\PayChangu\InitiateRequest;
use App\Services\PayChangu\PayChanguGateway;
use App\Services\Submissions\SubmissionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class PaymentInitiationService
{
    public function __construct(
        private readonly PayChanguGateway $gateway,
        private readonly SubmissionService $submissions,
    ) {}

    public static function feeFor(string $kind): int
    {
        $fees = config('services.submissions.fees', []);
        $override = Setting::get('submissions.fee_'.$kind);
        return (int) ($override ?? ($fees[$kind] ?? 5500));
    }

    /**
     * Kick off a PayChangu Standard Checkout session for a submission.
     * Returns the URL the caller should redirect the user to.
     */
    public function initiateForSubmission(Submission $submission, User $user): array
    {
        // Reuse an in-flight payment if one already exists in pending/processing.
        $payment = $submission->payments()
            ->where('status', Payment::STATUS_PENDING)
            ->latest()
            ->first();

        $amount = self::feeFor($submission->kind ?: Submission::KIND_SONG);
        $currency = (string) (Setting::get('submissions.fee_currency') ?? config('services.submissions.fee_currency', 'MWK'));

        if (!$payment) {
            $payment = DB::transaction(function () use ($submission, $user, $amount, $currency) {
                $payment = Payment::create([
                    'user_id' => $user->id,
                    'payable_type' => Submission::class,
                    'payable_id' => $submission->id,
                    'status' => Payment::STATUS_PENDING,
                    'provider' => Payment::PROVIDER_PAYCHANGU,
                    'amount' => $amount,
                    'currency' => $currency,
                    'initiated_at' => now(),
                ]);
                $this->submissions->markAwaitingPayment($submission);
                return $payment;
            });
        }

        $txRef = 'MAM-'.$submission->id.'-'.Str::lower(Str::random(10));
        $payment->update([
            'provider_reference' => $txRef,
            'status' => Payment::STATUS_PROCESSING,
        ]);

        [$firstName, $lastName] = $this->splitName($submission->submitter_name ?: $user->name);

        $result = $this->gateway->initiate(new InitiateRequest(
            txRef: $txRef,
            amount: $amount,
            currency: $currency,
            email: $submission->submitter_email ?: $user->email,
            firstName: $firstName ?: 'Guest',
            lastName: $lastName ?: 'User',
            callbackUrl: URL::to('/payments/webhook/paychangu'),
            returnUrl: URL::route('payments.return'),
            title: 'Music Submission Fee',
            description: 'Malawi Adventist Music — submission '.$submission->reference,
            meta: [
                'submission_id' => $submission->id,
                'submission_reference' => $submission->reference,
            ],
        ));

        PaymentTransaction::create([
            'payment_id' => $payment->id,
            'event_type' => PaymentTransaction::EVENT_INITIATE,
            'status' => $result->ok ? 'ok' : 'error',
            'payload' => $result->raw ?: ['error' => $result->error],
        ]);

        if (!$result->ok) {
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'failed_at' => now(),
                'failure_reason' => $result->error,
            ]);
            $this->submissions->markPaymentFailed($submission);
            throw new \RuntimeException('Failed to start payment: '.$result->error);
        }

        $payment->update([
            'checkout_url' => $result->checkoutUrl,
            'provider_payload' => $result->raw,
        ]);
        $this->submissions->markPaymentPending($submission);

        return [
            'checkout_url' => $result->checkoutUrl,
            'payment' => $payment->refresh(),
        ];
    }

    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2) ?: [];
        return [$parts[0] ?? '', $parts[1] ?? ''];
    }
}
