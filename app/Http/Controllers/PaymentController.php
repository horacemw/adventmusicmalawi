<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Services\PayChangu\PayChanguGateway;
use App\Services\Payments\PaymentVerificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PaymentController extends Controller
{
    /**
     * Browser return endpoint. PayChangu redirects the user here after the checkout.
     * We verify the transaction server-to-server before showing success.
     */
    public function return(Request $request, PaymentVerificationService $verifier)
    {
        $txRef = $request->query('tx_ref');
        if (!$txRef) {
            return Inertia::render('Payments/Return', [
                'status' => 'error',
                'message' => 'Missing transaction reference.',
            ]);
        }

        try {
            $payment = $verifier->verifyAndProgress($txRef, PaymentTransaction::EVENT_VERIFY, $request->ip());
        } catch (\RuntimeException $e) {
            return Inertia::render('Payments/Return', [
                'status' => 'error',
                'message' => $e->getMessage(),
                'tx_ref' => $txRef,
            ]);
        }

        return Inertia::render('Payments/Return', [
            'status' => $payment->status,
            'reference' => $payment->reference,
            'tx_ref' => $payment->provider_reference,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'failure_reason' => $payment->failure_reason,
        ]);
    }

    /**
     * PayChangu webhook — signed with HMAC-SHA256 of the raw body using our webhook secret.
     * Never mutate state before signature verification.
     */
    public function webhook(Request $request, PayChanguGateway $gateway, PaymentVerificationService $verifier): HttpResponse
    {
        $raw = $request->getContent();
        $signature = $request->header('Signature');

        if (!$gateway->verifyWebhookSignature($raw, $signature)) {
            Log::warning('PayChangu webhook signature verification failed', [
                'ip' => $request->ip(),
                'sig_present' => (bool) $signature,
            ]);
            return response('invalid signature', 401);
        }

        $payload = json_decode($raw, true) ?? [];
        $txRef = $payload['reference'] ?? $payload['tx_ref'] ?? null;

        if (!$txRef) {
            return response('missing reference', 422);
        }

        try {
            $verifier->verifyAndProgress($txRef, PaymentTransaction::EVENT_WEBHOOK, $request->ip());
        } catch (\RuntimeException $e) {
            // Unknown tx_ref — log but 200 to prevent retry loops
            Log::info('PayChangu webhook for unknown tx_ref', ['tx_ref' => $txRef, 'msg' => $e->getMessage()]);
        }

        return response('ok', 200);
    }
}
