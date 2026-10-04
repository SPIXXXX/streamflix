<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FilmController as AdminFilmController;
use App\Http\Controllers\Admin\MovieListController as AdminMovieListController;
use App\Http\Controllers\Admin\TeaserController as AdminTeaserController;
use App\Http\Controllers\AdminFilmDetailController;
use App\Http\Controllers\AdminTmdbLookupController;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MovieListController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewCommentController;
use App\Http\Controllers\ReviewReactionController;
use App\Http\Controllers\TeaserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check() && auth()->user()->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }

    return view('welcome');
});

Route::get('/dashboard', function () {
    if (auth()->check() && auth()->user()->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/reviews/{review}', [FilmController::class, 'updateReview'])->name('reviews.update');
    Route::delete('/reviews/{review}', [FilmController::class, 'destroyReview'])->name('reviews.destroy');

    // Review reactions (agree / disagree)
    Route::post('/reviews/{review}/reactions', [ReviewReactionController::class, 'store'])->name('reviews.reactions.store');
    Route::post('/reviews/{review}/comments', [ReviewCommentController::class, 'store'])->name('reviews.comments.store');
    Route::post('/films/{film}/favorite', [FilmController::class, 'toggleFavorite'])->name('films.favorite');
});

// Client-facing films
Route::get('/films', [FilmController::class, 'index'])->name('films.index');
Route::get('/films/collections/{category}', [FilmController::class, 'collection'])->name('films.collections');
Route::get('/films/{film}/cast/{personId}', [FilmController::class, 'castMember'])
    ->whereNumber(['film', 'personId'])
    ->name('films.cast-member');
Route::get('/films/{film}', [FilmController::class, 'show'])->name('films.show');
Route::middleware('auth')->post('/films/{film}/reviews', [FilmController::class, 'storeReview'])->name('films.reviews.store');

Route::get('/teasers', [TeaserController::class, 'index'])->name('teasers.index');

Route::get('/members', [MemberController::class, 'index'])->name('members.index');
Route::get('/members/{user}', [MemberController::class, 'show'])->name('members.show');

Route::get('/lists', [MovieListController::class, 'index'])->name('lists.index');
Route::middleware('auth')->get('/lists/favorites', [MovieListController::class, 'favorites'])->name('lists.favorites');

Route::middleware('auth')->group(function () {
    Route::get('/lists/create', [MovieListController::class, 'create'])->name('lists.create');
    Route::post('/lists', [MovieListController::class, 'store'])->name('lists.store');
    Route::get('/lists/{list}/edit', [MovieListController::class, 'edit'])->name('lists.edit');
    Route::put('/lists/{list}', [MovieListController::class, 'update'])->name('lists.update');
    Route::delete('/lists/{list}', [MovieListController::class, 'destroy'])->name('lists.destroy');
    Route::post('/lists/{list}/films', [MovieListController::class, 'addFilm'])->name('lists.films.add');
    Route::delete('/lists/{list}/films/{film}', [MovieListController::class, 'removeFilm'])->name('lists.films.remove');
});

Route::get('/lists/{list}', [MovieListController::class, 'show'])->name('lists.show');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('accounts/{user}', [AccountController::class, 'show'])->name('accounts.show');
    Route::patch('accounts/{user}/feature', [AccountController::class, 'feature'])->name('accounts.feature');
    Route::patch('accounts/{user}/suspend', [AccountController::class, 'suspend'])->name('accounts.suspend');
    Route::patch('accounts/{user}/ban', [AccountController::class, 'ban'])->name('accounts.ban');
    Route::patch('accounts/{user}/reactivate', [AccountController::class, 'reactivate'])->name('accounts.reactivate');
    Route::delete('accounts/{user}', [AccountController::class, 'destroy'])->name('accounts.destroy');

    Route::get('films/search', [AdminFilmController::class, 'search'])->name('films.search');
    Route::get('films/tmdb-search', [AdminFilmController::class, 'tmdbSearch'])->name('films.tmdb-search');
    Route::get('films/tmdb-results', [AdminTmdbLookupController::class, 'results'])->name('films.tmdb-results');
    Route::get('films/tmdb-details/{tmdbId}', [AdminTmdbLookupController::class, 'details'])->whereNumber('tmdbId')->name('films.tmdb-details');
    Route::post('films/bulk-store', [AdminFilmController::class, 'bulkStore'])->name('films.bulk-store');
    Route::post('films/import', [AdminFilmController::class, 'import'])->name('films.import');
    Route::resource('films', AdminFilmController::class)->except(['show']);
    Route::get('films/{film}', [AdminFilmDetailController::class, 'show'])->name('films.show');
    Route::get('lists/matching-films', [AdminMovieListController::class, 'matchingFilms'])->name('lists.matching-films');
    Route::get('lists/films/{film}/tmdb-metadata', [AdminMovieListController::class, 'movieMetadata'])->name('lists.films.metadata');
    Route::post('lists/{list}/films/sync', [AdminMovieListController::class, 'syncFilms'])->name('lists.films.sync');
    Route::get('lists/{list}/films', [AdminMovieListController::class, 'manageFilms'])->name('lists.manage-films');
    Route::get('lists/{list}/films/search', [AdminMovieListController::class, 'searchFilms'])->name('lists.films.search');
    Route::post('lists/{list}/films', [AdminMovieListController::class, 'addFilm'])->name('lists.films.add');
    Route::delete('lists/{list}/films/{film}', [AdminMovieListController::class, 'removeFilm'])->name('lists.films.remove');
    Route::resource('lists', AdminMovieListController::class)->except(['show']);
    Route::get('lists/{list}', [AdminMovieListController::class, 'show'])->name('lists.show');
    Route::resource('teasers', AdminTeaserController::class)->except(['show']);
});

require __DIR__.'/auth.php';
