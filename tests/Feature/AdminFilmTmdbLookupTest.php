<?php

use App\Models\Film;
use App\Models\User;
use App\Services\TmdbService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;

it('admin can look up a movie from tmdb for autofill', function () {
    Role::firstOrCreate(['name' => 'admin']);

    $user = User::factory()->create();
    $user->assignRole('admin');
    app()->instance(TmdbService::class, new class extends TmdbService
    {
        public function search(string $query): array
        {
            return [['id' => 42, 'title' => 'Inception', 'poster_path' => '/inception.jpg']];
        }

        public function details(int $tmdbId): array
        {
            return [
                'id' => $tmdbId,
                'title' => 'Inception',
                'poster_path' => '/inception.jpg',
                'release_date' => '2010-07-16',
                'genres' => [],
                'credits' => ['cast' => []],
            ];
        }
    });

    $response = $this->actingAs($user)->getJson('/admin/films/tmdb-search?q=Inception');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'title',
            'synopsis',
            'genre',
            'release_date',
            'release_year',
            'cast',
            'poster_path',
            'poster_url',
        ])
        ->assertJsonPath('poster_path', '/inception.jpg');
});

it('caches normalized TMDB searches and returns compact result data without details requests', function () {
    Role::firstOrCreate(['name' => 'admin']);
    config(['services.tmdb.token' => 'test-token', 'services.tmdb.key' => null]);
    $user = User::factory()->create();
    $user->assignRole('admin');
    Cache::flush();
    Http::fake([
        'api.themoviedb.org/3/search/movie*' => Http::response(['results' => [[
            'id' => 42,
            'title' => 'Interstellar',
            'original_title' => 'Interstellar',
            'overview' => 'A space film.',
            'release_date' => '2014-11-07',
            'poster_path' => '/interstellar.jpg',
            'backdrop_path' => '/space.jpg',
            'adult' => false,
        ]]]),
    ]);

    $this->actingAs($user)->getJson(route('admin.films.tmdb-results', ['q' => 'Interstellar']))
        ->assertOk()
        ->assertJsonPath('results.0.tmdb_id', 42)
        ->assertJsonPath('results.0.poster_url', 'https://image.tmdb.org/t/p/w185/interstellar.jpg')
        ->assertJsonMissingPath('results.0.backdrop_path')
        ->assertJsonMissingPath('results.0.adult');

    $this->actingAs($user)->getJson(route('admin.films.tmdb-results', ['q' => '  INTERSTELLAR ']))->assertOk();

    Http::assertSentCount(1);
    Http::assertSent(fn ($request) => str_contains($request->url(), '/search/movie') && $request['query'] === 'interstellar');
});

it('loads movie page cast from the cached credits endpoint without fetching unused movie details', function () {
    config(['services.tmdb.token' => 'test-token', 'services.tmdb.key' => null]);
    Cache::flush();
    Http::fake([
        'api.themoviedb.org/3/movie/42*' => Http::response([
            'id' => 42,
            'credits' => ['cast' => [[
                'id' => 7,
                'name' => 'Lead Actor',
                'character' => 'The Pilot',
                'order' => 0,
                'profile_path' => '/lead.jpg',
            ]]],
        ]),
        'api.themoviedb.org/3/person/7*' => Http::response([
            'id' => 7,
            'name' => 'Lead Actor',
            'biography' => 'A performer biography.',
            'birthday' => '1980-01-01',
            'place_of_birth' => 'London',
            'known_for_department' => 'Acting',
            'profile_path' => '/lead.jpg',
        ]),
    ]);
    $film = Film::create(['tmdb_id' => 42, 'title' => 'Interstellar', 'genre' => 'Drama']);

    $this->get(route('films.show', $film))
        ->assertOk()
        ->assertSee('Lead Actor')
        ->assertSee('The Pilot');

    Http::assertSentCount(1);
    Http::assertSent(fn ($request) => str_contains($request->url(), '/movie/42') && $request['append_to_response'] === 'credits');

    $this->getJson(route('films.cast-member', ['personId' => 7]))
        ->assertOk()
        ->assertJsonPath('biography', 'A performer biography.')
        ->assertJsonPath('known_for_department', 'Acting')
        ->assertJsonPath('profile_url', 'https://image.tmdb.org/t/p/w500/lead.jpg');

    Http::assertSentCount(2);
});
