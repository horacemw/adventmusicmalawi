<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
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
Route::get('/submit-music', fn () => Inertia::render('Placeholder', ['title' => 'Submit Music']))->name('submissions.create');
Route::get('/about', fn () => Inertia::render('Placeholder', ['title' => 'About']))->name('about');
Route::get('/contact', fn () => Inertia::render('Placeholder', ['title' => 'Contact']))->name('contact');
Route::get('/terms', fn () => Inertia::render('Placeholder', ['title' => 'Terms']))->name('terms');
Route::get('/privacy', fn () => Inertia::render('Placeholder', ['title' => 'Privacy']))->name('privacy');
Route::get('/copyright', fn () => Inertia::render('Placeholder', ['title' => 'Copyright']))->name('copyright');

Route::get('/dashboard', fn () => Inertia::render('Dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
