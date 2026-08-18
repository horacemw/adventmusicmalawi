# Payments — PayChangu integration plan

**Status:** DB schema and models are in place. Wire integration is deferred to the submissions phase.

## What's ready
- `payments` table with `provider`, `provider_reference`, `checkout_url`, `provider_payload`, `provider_response`, status enum (`pending | processing | successful | failed | cancelled | refunded`), and `payable_type` + `payable_id` for polymorphism (submission / promotion / ad campaign).
- `payment_transactions` — append-only audit log of every event (`initiate | callback | verify | refund | webhook`) with full JSON payload and IP address.
- `App\Models\Payment` with reference UUID, status constants, casts.
- `.env` slots for PayChangu keys and webhook secret.

## Integration checklist (later session)

1. **Fetch official PayChangu docs.** Do not invent endpoints. Verify current API version.
2. Create `App\Services\PayChangu\Client` — thin HTTP client with API key auth.
3. Create `App\Services\Payments\SubmissionPaymentService`:
   - `initiate(Submission $s, User $u): Payment` — creates a `Payment` row (status `pending`), calls PayChangu initiate, stores `checkout_url` + `provider_reference`, logs a `payment_transactions` row with `event_type=initiate`.
4. Callback controller:
   - Route: `POST /payments/webhook/paychangu`.
   - Middleware: verify webhook signature using `PAYCHANGU_WEBHOOK_SECRET` before any DB write.
   - Idempotency: `payments.provider_reference` unique — reject duplicate deliveries.
   - Verify amount + currency match `Payment` row; reject if not.
   - On success: mark `Payment` successful, advance the linked submission from `awaiting_payment` → `under_review`.
5. Return controller: `GET /payments/return` for the user redirect.
6. Verify controller: `GET /payments/{payment}/verify` — server-to-server verification triggered from the UI before showing success.
7. Never trust `?status=success` from the frontend. Always re-verify server-side.
8. Failure paths:
   - Payment failed → `Payment` → failed, submission → `awaiting_payment` (user can retry).
   - Rejection with refund enabled (`settings.submissions.allow_refund_on_reject = true`) → dispatch a refund job.

## State machine

```
Payment:
   pending → processing → successful
              ↘ failed
              ↘ cancelled
   successful → refunded (moderator action)

Submission:
   draft → awaiting_payment → payment_pending → paid → under_review
                                                           ↘ approved → published
                                                           ↘ rejected
                                                           ↘ changes_requested → back to under_review
   any → withdrawn (user action)
```

Successful payment moves `awaiting_payment → under_review`, **not** to `approved`. Admin must approve.

## Security requirements
- All PayChangu keys stored in `.env`, never in code.
- Webhook signature verified on every delivery.
- Rate-limit callback endpoints.
- All money handling in decimal(12,2), currency stored per row.
- Amount verified server-side against the fee configured in `settings.submissions.fee_amount`.
- Duplicate `provider_reference` rejected.
- All payment events logged to `payment_transactions` with IP address.
