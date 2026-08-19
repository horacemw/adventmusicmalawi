<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Placeholder discovery routes — filled in during later phases.
Route::get('/discover', fn () => Inertia::render('Placeholder', ['title' => 'Discover']))->name('discover');
Route::get('/songs', fn () => Inertia::render('Placeholder', ['title' => 'Songs']))->name('songs.index');
Route::get('/albums', fn () => Inertia::render('Placeholder', ['title' => 'Albums']))->name('albums.index');
Route::get('/artists', fn () => Inertia::render('Placeholder', ['title' => 'Artists']))->name('artists.index');
Route::get('/groups', fn () => Inertia::render('Placeholder', ['title' => 'Groups & Choirs']))->name('groups.index');
Route::get('/churches', fn () => Inertia::render('Placeholder', ['title' => 'Churches']))->name('churches.index');
Route::get('/hymn-books', fn () => Inertia::render('Placeholder', ['title' => 'Hymn Books']))->name('hymn-books.index');
Route::get('/occasions', fn () => Inertia::render('Placeholder', ['title' => 'Occasions']))->name('occasions.index');
Route::get('/trending', fn () => Inertia::render('Placeholder', ['title' => 'Trending']))->name('trending');
Route::get('/top-100', fn () => Inertia::render('Placeholder', ['title' => 'Top 100']))->name('top-100');
Route::get('/search', fn () => Inertia::render('Placeholder', ['title' => 'Search']))->name('search');

// Content pages (accessible without login)
Route::get('/about', fn () => Inertia::render('About'))->name('about');
Route::get('/contact', fn () => Inertia::render('Contact'))->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::get('/terms', fn () => Inertia::render('Legal', ['kind' => 'terms']))->name('terms');
Route::get('/privacy', fn () => Inertia::render('Legal', ['kind' => 'privacy']))->name('privacy');
Route::get('/copyright', fn () => Inertia::render('Legal', ['kind' => 'copyright']))->name('copyright');
Route::get('/settings', fn () => Inertia::render('Settings'))->name('settings');

// Public playlist view — public/unlisted playlists visible without auth.
// {playlist} constrained to digits so `/playlists/new` (below, auth-only) doesn't get
// swallowed by route model binding trying to find a playlist with id="new".
Route::get('/playlists/{playlist}', [PlaylistController::class, 'show'])
    ->where('playlist', '[0-9]+')
    ->name('playlists.show');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Playlists — auth required; visibility of individual playlists handled in controller
    Route::get('/playlists', [PlaylistController::class, 'index'])->name('playlists.index');
    Route::get('/playlists/new', [PlaylistController::class, 'create'])->name('playlists.create');
    Route::post('/playlists', [PlaylistController::class, 'store'])->name('playlists.store');
    Route::patch('/playlists/{playlist}', [PlaylistController::class, 'update'])->name('playlists.update');
    Route::delete('/playlists/{playlist}', [PlaylistController::class, 'destroy'])->name('playlists.destroy');
    Route::post('/playlists/{playlist}/songs', [PlaylistController::class, 'addSong'])->name('playlists.songs.store');
    Route::delete('/playlists/{playlist}/songs/{song}', [PlaylistController::class, 'removeSong'])->name('playlists.songs.destroy');

    // Submissions — require email verification because submitters get payment + moderation emails
    Route::middleware('verified')->group(function () {
        Route::get('/submissions', [SubmissionController::class, 'index'])->name('submissions.index');
        Route::get('/submit-music', [SubmissionController::class, 'create'])->name('submissions.create');
        Route::get('/submissions/{submission}/edit', [SubmissionController::class, 'edit'])->name('submissions.edit');
        Route::put('/submissions/{submission}', [SubmissionController::class, 'update'])->name('submissions.update');
        Route::post('/submissions/{submission}/files', [SubmissionController::class, 'uploadFile'])->name('submissions.files.store');
        Route::delete('/submissions/{submission}/files/{file}', [SubmissionController::class, 'deleteFile'])->name('submissions.files.destroy');
        Route::post('/submissions/{submission}/pay', [SubmissionController::class, 'submitForPayment'])->name('submissions.pay');
    });
});

// Payments — return is a browser redirect (auth optional), webhook must not require auth
Route::get('/payments/return', [PaymentController::class, 'return'])->name('payments.return');
Route::post('/payments/webhook/paychangu', [PaymentController::class, 'webhook'])
    ->name('payments.webhook.paychangu');

// Dev-only impersonation for screenshots + local testing. Guarded by APP_ENV=local.
if (app()->environment('local')) {
    Route::get('/_dev/impersonate/{email}', function (string $email) {
        $user = \App\Models\User::where('email', $email)->firstOrFail();
        if (!$user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }
        \Illuminate\Support\Facades\Auth::login($user);
        return redirect('/')->with('impersonated', $email);
    })->name('_dev.impersonate');
}

require __DIR__.'/auth.php';
