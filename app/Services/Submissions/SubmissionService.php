<?php

namespace App\Services\Submissions;

use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SubmissionService
{
    /**
     * Create a fresh draft submission for the given user.
     * Fills submitter contact from the user's profile as a starting point.
     */
    public function createDraft(User $user): Submission
    {
        return Submission::create([
            'user_id' => $user->id,
            'submitter_name' => $user->name,
            'submitter_email' => $user->email,
            'submitter_phone' => $user->phone,
            'song_title' => '',
            'status' => Submission::STATUS_DRAFT,
        ]);
    }

    /**
     * Update the submission with validated fields. Blocked once payment has been initiated.
     */
    public function updateDetails(Submission $submission, array $data): Submission
    {
        $this->ensureEditable($submission);
        $submission->fill($data)->save();

        if (isset($data['category_ids'])) {
            $submission->categories()->sync($data['category_ids']);
        }
        if (isset($data['occasion_ids'])) {
            $submission->occasions()->sync($data['occasion_ids']);
        }
        if (isset($data['mood_ids'])) {
            $submission->moods()->sync($data['mood_ids']);
        }

        return $submission->refresh();
    }

    /**
     * Store an uploaded file against the submission. Returns the created SubmissionFile row.
     */
    public function attachFile(Submission $submission, UploadedFile $file, string $kind): SubmissionFile
    {
        $this->ensureEditable($submission);

        $path = $file->store("submissions/{$submission->id}", 'public');
        $absolute = Storage::disk('public')->path($path);
        $checksum = @hash_file('sha256', $absolute) ?: null;

        // If a file of the same kind already exists (audio/artwork), replace it.
        $submission->files()->where('kind', $kind)->get()->each(function (SubmissionFile $existing) {
            if ($existing->storage_path) {
                Storage::disk('public')->delete($existing->storage_path);
            }
            $existing->delete();
        });

        return $submission->files()->create([
            'kind' => $kind,
            'original_name' => $file->getClientOriginalName(),
            'storage_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'checksum_sha256' => $checksum,
        ]);
    }

    /**
     * Mark the submission as awaiting payment. Called just before initiating the PayChangu session.
     */
    public function markAwaitingPayment(Submission $submission): Submission
    {
        $this->ensureEditable($submission);

        // Require all critical fields + files
        if (
            !$submission->song_title
            || !$submission->owner_confirmation
            || !$submission->platform_distribution_permission
            || !$submission->accuracy_confirmation
        ) {
            throw new \DomainException('Submission is missing required confirmations before payment.');
        }
        if (!$submission->files()->where('kind', SubmissionFile::KIND_AUDIO)->exists()) {
            throw new \DomainException('An audio file is required before payment.');
        }

        $submission->update(['status' => Submission::STATUS_AWAITING_PAYMENT]);
        return $submission->refresh();
    }

    public function markPaymentPending(Submission $submission): void
    {
        $submission->update(['status' => Submission::STATUS_PAYMENT_PENDING]);
    }

    public function markPaid(Submission $submission): void
    {
        if (in_array($submission->status, [
            Submission::STATUS_APPROVED,
            Submission::STATUS_PUBLISHED,
        ], true)) {
            return; // already progressed further
        }
        $submission->update(['status' => Submission::STATUS_UNDER_REVIEW]);
    }

    public function markPaymentFailed(Submission $submission): void
    {
        if (in_array($submission->status, [
            Submission::STATUS_PAID,
            Submission::STATUS_UNDER_REVIEW,
            Submission::STATUS_APPROVED,
            Submission::STATUS_PUBLISHED,
        ], true)) {
            return;
        }
        $submission->update(['status' => Submission::STATUS_AWAITING_PAYMENT]);
    }

    /**
     * Prevent editing after payment has been initiated.
     */
    private function ensureEditable(Submission $submission): void
    {
        $locked = [
            Submission::STATUS_PAID,
            Submission::STATUS_UNDER_REVIEW,
            Submission::STATUS_APPROVED,
            Submission::STATUS_PUBLISHED,
            Submission::STATUS_WITHDRAWN,
        ];
        if (in_array($submission->status, $locked, true)) {
            throw new \DomainException('Submission is locked and cannot be edited: '.$submission->status);
        }
    }
}
