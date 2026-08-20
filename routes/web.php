<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscoverController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfilesController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Discovery / browse pages
Route::get('/discover', [DiscoverController::class, 'index'])->name('discover');
Route::get('/songs', [DiscoverController::class, 'songs'])->name('songs.index');
Route::get('/albums', [DiscoverController::class, 'albums'])->name('albums.index');
Route::get('/artists', [DiscoverController::class, 'artists'])->name('artists.index');
Route::get('/groups', [DiscoverController::class, 'groups'])->name('groups.index');
Route::get('/churches', [DiscoverController::class, 'churches'])->name('churches.index');
Route::get('/hymn-books', [DiscoverController::class, 'hymnBooks'])->name('hymn-books.index');
Route::get('/poems', [DiscoverController::class, 'poems'])->name('poems.index');
Route::get('/occasions', [DiscoverController::class, 'occasions'])->name('occasions.index');
Route::get('/occasions/{occasion:slug}', [DiscoverController::class, 'occasion'])->name('occasions.show');
Route::get('/trending', [DiscoverController::class, 'trending'])->name('trending');
Route::get('/top-100', [DiscoverController::class, 'top100'])->name('top-100');
Route::get('/search', [DiscoverController::class, 'search'])->name('search');

// Detail / profile pages
Route::get('/songs/{song:slug}', [ProfilesController::class, 'song'])->name('songs.show');
Route::get('/albums/{album:slug}', [ProfilesController::class, 'album'])->name('albums.show');
Route::get('/artists/{artist:slug}', [ProfilesController::class, 'artist'])->name('artists.show');
Route::get('/groups/{group:slug}', [ProfilesController::class, 'group'])->name('groups.show');
Route::get('/churches/{church:slug}', [ProfilesController::class, 'church'])->name('churches.show');
Route::get('/poems/{poem:slug}', [ProfilesController::class, 'poem'])->name('poems.show');

// Content pages (accessible without login)
Route::get('/about', fn () => Inertia::render('About'))->name('about');
Route::get('/contact', fn () => Inertia::render('Contact'))->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::get('/terms', fn () => Inertia::render('Legal', ['kind' => 'terms']))->name('terms');
Route::get('/privacy', fn () => Inertia::render('Legal', ['kind' => 'privacy']))->name('privacy');
Route::get('/copyright', fn () => Inertia::render('Legal', ['kind' => 'copyright']))->name('copyright');
Route::get('/settings', fn () => Inertia::render('Settings'))->name('settings');

// Public song download — requires auth so we can track user; no verify required for MVP.
Route::get('/download/song/{song:slug}', [DownloadController::class, 'song'])
    ->middleware('auth')
    ->name('downloads.song');

// Playback analytics — called by the persistent player after each listen.
// No auth requirement (anonymous listeners still generate valid streams);
// session cookie is used to de-dupe counted streams within an hour.
Route::post('/api/streams', [StreamController::class, 'store'])->name('streams.store');

// Likes — liking is a low-friction action; email verification not required.
Route::middleware('auth')->group(function () {
    Route::get('/liked-songs', [LikeController::class, 'index'])->name('likes.index');
    Route::post('/songs/{song:slug}/like', [LikeController::class, 'toggle'])->name('songs.like');
});

// Public playlist view — public/unlisted playlists visible without auth.
// {playlist} constrained to digits so `/playlists/new` (below, auth-only) doesn't get
// swallowed by route model binding trying to find a playlist with id="new".
Route::get('/playlists/{playlist}', [PlaylistController::class, 'show'])
    ->where('playlist', '[0-9]+')
    ->name('playlists.show');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Every authenticated route now requires a verified email.
// Profile lives outside the `verified` group so a user can still edit
// their email if they mistyped it during registration.
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Playlists
    Route::get('/playlists', [PlaylistController::class, 'index'])->name('playlists.index');
    Route::get('/playlists/new', [PlaylistController::class, 'create'])->name('playlists.create');
    Route::post('/playlists', [PlaylistController::class, 'store'])->name('playlists.store');
    Route::patch('/playlists/{playlist}', [PlaylistController::class, 'update'])->name('playlists.update');
    Route::delete('/playlists/{playlist}', [PlaylistController::class, 'destroy'])->name('playlists.destroy');
    Route::post('/playlists/{playlist}/songs', [PlaylistController::class, 'addSong'])->name('playlists.songs.store');
    Route::delete('/playlists/{playlist}/songs/{song}', [PlaylistController::class, 'removeSong'])->name('playlists.songs.destroy');

    // Submissions
    Route::get('/submissions', [SubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/submit-music', [SubmissionController::class, 'create'])->name('submissions.create');
    Route::get('/submissions/{submission}/edit', [SubmissionController::class, 'edit'])->name('submissions.edit');
    Route::put('/submissions/{submission}', [SubmissionController::class, 'update'])->name('submissions.update');
    Route::post('/submissions/{submission}/files', [SubmissionController::class, 'uploadFile'])->name('submissions.files.store');
    Route::delete('/submissions/{submission}/files/{file}', [SubmissionController::class, 'deleteFile'])->name('submissions.files.destroy');
    Route::post('/submissions/{submission}/pay', [SubmissionController::class, 'submitForPayment'])->name('submissions.pay');
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
