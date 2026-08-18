<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\District;
use App\Models\Genre;
use App\Models\Language;
use App\Models\Mood;
use App\Models\Occasion;
use App\Models\Region;
use App\Models\Setting;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Services\Payments\PaymentInitiationService;
use App\Services\Submissions\SubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class SubmissionController extends Controller
{
    public function __construct(private readonly SubmissionService $submissions) {}

    /** List the current user's submissions. */
    public function index(Request $request): Response
    {
        $items = Submission::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get(['id', 'reference', 'song_title', 'status', 'song_id', 'created_at']);

        return Inertia::render('Submissions/Index', [
            'submissions' => $items,
        ]);
    }

    /** Show the multi-step submission wizard. Creates a draft if none exists. */
    public function create(Request $request): RedirectResponse|Response
    {
        $user = $request->user();

        // Reuse the most recent editable draft if the user has one
        $submission = Submission::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [
                Submission::STATUS_DRAFT,
                Submission::STATUS_AWAITING_PAYMENT,
                Submission::STATUS_CHANGES_REQUESTED,
            ])
            ->latest()
            ->first();

        if (!$submission) {
            $submission = $this->submissions->createDraft($user);
        }

        return $this->showWizard($submission);
    }

    public function edit(Request $request, Submission $submission): Response
    {
        $this->authorizeOwn($request, $submission);
        return $this->showWizard($submission);
    }

    /** Save step 1 & 2 (details). */
    public function update(Request $request, Submission $submission): RedirectResponse
    {
        $this->authorizeOwn($request, $submission);

        $data = $request->validate([
            'submitter_name' => 'required|string|max:255',
            'submitter_email' => 'required|email|max:255',
            'submitter_phone' => 'nullable|string|max:32',
            'song_title' => 'required|string|max:255',
            'artist_name' => 'nullable|string|max:255',
            'group_name' => 'nullable|string|max:255',
            'choir_name' => 'nullable|string|max:255',
            'church_name' => 'nullable|string|max:255',
            'album_title' => 'nullable|string|max:255',
            'release_year' => 'nullable|integer|between:1900,2100',
            'description' => 'nullable|string|max:5000',
            'language_id' => 'nullable|exists:languages,id',
            'genre_id' => 'nullable|exists:genres,id',
            'region_id' => 'nullable|exists:regions,id',
            'district_id' => 'nullable|exists:districts,id',
            'copyright_owner' => 'nullable|string|max:255',
            'rights_holder' => 'nullable|string|max:255',
            'permission_status' => 'nullable|in:owned,licensed,permission_granted,public_domain,unknown',
            'owner_confirmation' => 'boolean',
            'platform_distribution_permission' => 'boolean',
            'accuracy_confirmation' => 'boolean',
            'copyright_notes' => 'nullable|string|max:2000',
            'category_ids' => 'array',
            'category_ids.*' => 'exists:categories,id',
            'occasion_ids' => 'array',
            'occasion_ids.*' => 'exists:occasions,id',
            'mood_ids' => 'array',
            'mood_ids.*' => 'exists:moods,id',
        ]);

        try {
            $this->submissions->updateDetails($submission, $data);
        } catch (\DomainException $e) {
            return back()->withErrors(['submission' => $e->getMessage()]);
        }

        return back()->with('success', 'Saved');
    }

    /** Attach a file (audio, artwork, permission document). */
    public function uploadFile(Request $request, Submission $submission): RedirectResponse
    {
        $this->authorizeOwn($request, $submission);

        $maxAudioMb = (int) (Setting::get('uploads.max_audio_mb') ?? 50);
        $maxImageMb = (int) (Setting::get('uploads.max_image_mb') ?? 5);
        $allowedAudio = (array) (Setting::get('uploads.audio_mime_types') ?? [
            'audio/mpeg', 'audio/mp4', 'audio/aac', 'audio/wav', 'audio/x-wav',
        ]);

        $kind = $request->input('kind');
        $rules = match ($kind) {
            SubmissionFile::KIND_AUDIO => [
                'file' => [
                    'required', 'file',
                    'max:'.($maxAudioMb * 1024),
                    'mimetypes:'.implode(',', $allowedAudio),
                ],
            ],
            SubmissionFile::KIND_ARTWORK, SubmissionFile::KIND_ARTIST_IMAGE => [
                'file' => ['required', 'file', 'image', 'max:'.($maxImageMb * 1024)],
            ],
            SubmissionFile::KIND_PERMISSION => [
                'file' => ['required', 'file', 'max:'.($maxImageMb * 1024), 'mimes:pdf,png,jpg,jpeg'],
            ],
            default => [],
        };
        if (!$rules) {
            return back()->withErrors(['kind' => 'Unknown file kind.']);
        }

        $data = $request->validate($rules + ['kind' => 'required|string']);
        try {
            $this->submissions->attachFile($submission, $data['file'], $kind);
        } catch (\DomainException $e) {
            return back()->withErrors(['submission' => $e->getMessage()]);
        }

        return back()->with('success', 'Uploaded');
    }

    /** Delete an uploaded file. */
    public function deleteFile(Request $request, Submission $submission, SubmissionFile $file): RedirectResponse
    {
        $this->authorizeOwn($request, $submission);
        abort_unless($file->submission_id === $submission->id, 404);

        if ($file->storage_path) {
            Storage::disk('public')->delete($file->storage_path);
        }
        $file->delete();

        return back()->with('success', 'Removed');
    }

    /** Submit the wizard — start payment. */
    public function submitForPayment(Request $request, Submission $submission, PaymentInitiationService $payments): RedirectResponse
    {
        $this->authorizeOwn($request, $submission);

        try {
            $result = $payments->initiateForSubmission($submission, $request->user());
        } catch (\DomainException|\RuntimeException $e) {
            return back()->withErrors(['submission' => $e->getMessage()]);
        }

        return redirect()->away($result['checkout_url']);
    }

    private function showWizard(Submission $submission): Response
    {
        $submission->load(['categories:id', 'occasions:id', 'moods:id', 'files']);

        return Inertia::render('Submissions/Wizard', [
            'submission' => [
                'id' => $submission->id,
                'reference' => $submission->reference,
                'status' => $submission->status,
                'submitter_name' => $submission->submitter_name,
                'submitter_email' => $submission->submitter_email,
                'submitter_phone' => $submission->submitter_phone,
                'song_title' => $submission->song_title,
                'artist_name' => $submission->artist_name,
                'group_name' => $submission->group_name,
                'choir_name' => $submission->choir_name,
                'church_name' => $submission->church_name,
                'album_title' => $submission->album_title,
                'release_year' => $submission->release_year,
                'description' => $submission->description,
                'language_id' => $submission->language_id,
                'genre_id' => $submission->genre_id,
                'region_id' => $submission->region_id,
                'district_id' => $submission->district_id,
                'copyright_owner' => $submission->copyright_owner,
                'rights_holder' => $submission->rights_holder,
                'permission_status' => $submission->permission_status,
                'owner_confirmation' => (bool) $submission->owner_confirmation,
                'platform_distribution_permission' => (bool) $submission->platform_distribution_permission,
                'accuracy_confirmation' => (bool) $submission->accuracy_confirmation,
                'copyright_notes' => $submission->copyright_notes,
                'category_ids' => $submission->categories->pluck('id'),
                'occasion_ids' => $submission->occasions->pluck('id'),
                'mood_ids' => $submission->moods->pluck('id'),
                'files' => $submission->files->map(fn ($f) => [
                    'id' => $f->id,
                    'kind' => $f->kind,
                    'original_name' => $f->original_name,
                    'size_bytes' => $f->size_bytes,
                    'url' => $f->storage_path ? Storage::disk('public')->url($f->storage_path) : null,
                ]),
            ],
            'options' => [
                'languages' => Language::orderBy('sort_order')->get(['id', 'name']),
                'genres' => Genre::orderBy('name')->get(['id', 'name']),
                'categories' => Category::orderBy('sort_order')->get(['id', 'name']),
                'occasions' => Occasion::orderBy('sort_order')->get(['id', 'name']),
                'moods' => Mood::orderBy('sort_order')->get(['id', 'name']),
                'regions' => Region::orderBy('name')->get(['id', 'name']),
                'districts' => District::orderBy('name')->get(['id', 'name', 'region_id']),
            ],
            'fee' => [
                'amount' => (int) (Setting::get('submissions.fee_amount') ?? config('services.submissions.fee_amount', 15000)),
                'currency' => (string) (Setting::get('submissions.fee_currency') ?? config('services.submissions.fee_currency', 'MWK')),
            ],
        ]);
    }

    private function authorizeOwn(Request $request, Submission $submission): void
    {
        abort_unless($submission->user_id === $request->user()->id, 403);
    }
}
