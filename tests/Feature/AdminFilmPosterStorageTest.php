<?php

use App\Models\Film;
use App\Models\User;
use App\Services\TmdbService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

function posterAdmin(): User
{
    Role::firstOrCreate(['name' => 'admin']);
    $user = User::factory()->create();
    $user->assignRole('admin');

    return $user;
}

test('tmdb imports store poster paths without downloading poster images', function () {
    Storage::fake('public');
    Http::fake();
    app()->instance(TmdbService::class, new class extends TmdbService
    {
        public function details(int $tmdbId): array
        {
            return [
                'id' => $tmdbId,
                'title' => 'Faraway Worlds',
                'poster_path' => '/faraway-worlds.jpg',
                'release_date' => '2024-03-01',
                'genres' => [],
                'credits' => ['cast' => []],
                'keywords' => ['keywords' => []],
            ];
        }
    });

    $this->actingAs(posterAdmin())
        ->post(route('admin.films.import'), ['tmdb_id' => 987654])
        ->assertRedirect(route('admin.films.index'));

    $film = Film::where('tmdb_id', 987654)->firstOrFail();
    expect($film->poster_path)->toBe('/faraway-worlds.jpg')
        ->and($film->posterUrl())->toBe('https://image.tmdb.org/t/p/w500/faraway-worlds.jpg');
    Storage::disk('public')->assertDirectoryEmpty('posters');
    Http::assertNothingSent();
});

test('admin can save a tmdb poster path without downloading it', function () {
    Storage::fake('public');
    Http::fake();

    $this->actingAs(posterAdmin())
        ->post(route('admin.films.store'), [
            'title' => 'Remote Poster Film',
            'tmdb_id' => 987655,
            'tmdb_poster_path' => '/remote-poster.jpg',
        ])
        ->assertRedirect(route('admin.films.index'));

    $film = Film::where('tmdb_id', 987655)->firstOrFail();
    expect($film->poster_path)->toBe('/remote-poster.jpg')
        ->and($film->posterUrl())->toBe('https://image.tmdb.org/t/p/w500/remote-poster.jpg');
    Storage::disk('public')->assertDirectoryEmpty('posters');
    Http::assertNothingSent();
});

test('manual poster uploads remain stored on the public disk', function () {
    Storage::fake('public');

    $this->actingAs(posterAdmin())
        ->post(route('admin.films.store'), [
            'title' => 'Uploaded Poster Film',
            'poster' => UploadedFile::fake()->image('uploaded.jpg'),
        ])
        ->assertRedirect(route('admin.films.index'));

    $film = Film::where('title', 'Uploaded Poster Film')->firstOrFail();
    expect($film->poster_path)->toStartWith('posters/')
        ->and($film->posterUrl())->toBe(Storage::disk('public')->url($film->poster_path));
    Storage::disk('public')->assertExists($film->poster_path);
});
