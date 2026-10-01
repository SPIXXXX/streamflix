<?php

use App\Models\Film;
use App\Models\MovieList;
use App\Models\TmdbKeyword;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

function adminListUser(): User
{
    Role::firstOrCreate(['name' => 'admin']);
    $user = User::factory()->create();
    $user->assignRole('admin');

    return $user;
}

test('admin can create a classified official list and manage its existing films', function () {
    $admin = adminListUser();
    $film = Film::create(['title' => 'Orbit', 'genre' => 'Science Fiction', 'release_year' => 2024]);
    $theme = TmdbKeyword::create(['tmdb_id' => 7654321, 'name' => 'Space Exploration']);
    $theme->films()->attach($film);

    $create = $this->actingAs($admin)->post(route('admin.lists.store'), [
        'title' => 'Essential Space Films',
        'description' => 'A themed collection',
        'classification_type' => 'theme',
        'classifications' => ['Space Exploration'],
        'film_ids' => [$film->id],
    ]);

    $list = MovieList::where('title', 'Essential Space Films')->firstOrFail();
    $create->assertRedirect(route('admin.lists.show', $list));
    expect($list->is_official)->toBeTrue()
        ->and($list->is_public)->toBeTrue()
        ->and($list->classification_type)->toBe('theme');
    $this->assertDatabaseHas('list_films', ['movie_list_id' => $list->id, 'film_id' => $film->id]);

    $genreCreate = $this->post(route('admin.lists.store'), [
        'title' => 'Science Fiction Essentials',
        'classification_type' => 'genre',
        'classification' => 'Science Fiction',
    ]);
    $genreList = MovieList::where('title', 'Science Fiction Essentials')->firstOrFail();
    $genreCreate->assertRedirect(route('admin.lists.show', $genreList));
    expect($genreList->classification_type)->toBe('genre')
        ->and($genreList->classification)->toBe('Science Fiction');

    $this->post(route('admin.lists.films.add', $list), ['film_id' => $film->id])
        ->assertRedirect();
    $this->assertDatabaseHas('list_films', ['movie_list_id' => $list->id, 'film_id' => $film->id]);

    $this->get(route('admin.lists.manage-films', $list))->assertOk()->assertSee('Manage Films');
    $this->delete(route('admin.lists.films.remove', [$list, $film]))->assertRedirect();
    $this->assertDatabaseMissing('list_films', ['movie_list_id' => $list->id, 'film_id' => $film->id]);
    $this->assertDatabaseHas('films', ['id' => $film->id, 'title' => 'Orbit']);
});

test('official list creation selects real matching movies and bulk management preserves catalog films', function () {
    $admin = adminListUser();
    $scienceFictionFilm = Film::create(['title' => 'Nebula', 'genre' => 'Science Fiction', 'release_year' => 2025]);
    $dramaFilm = Film::create(['title' => 'Quiet River', 'genre' => 'Drama', 'release_year' => 2023]);

    $this->actingAs($admin)->get(route('admin.lists.create'))
        ->assertOk()
        ->assertSee('Create Official List')
        ->assertSee('adminListFilmPicker', false)
        ->assertDontSee('create-official-list-drawer');

    $this->get(route('admin.lists.matching-films', [
        'classification_type' => 'genre',
        'classification' => 'Science Fiction',
        'matching' => 1,
    ]))->assertOk()->assertJsonCount(1, 'films')->assertJsonPath('films.0.title', 'Nebula');

    $this->post(route('admin.lists.store'), [
        'title' => 'Invalid Selection',
        'classification_type' => 'genre',
        'classification' => 'Science Fiction',
        'film_ids' => [999999],
    ])->assertSessionHasErrors('film_ids.0');
    $this->assertDatabaseMissing('movie_lists', ['title' => 'Invalid Selection']);

    $this->post(route('admin.lists.store'), [
        'title' => 'Space Features',
        'classification_type' => 'genre',
        'classification' => 'Science Fiction',
        'film_ids' => [$scienceFictionFilm->id],
    ])->assertSessionHasNoErrors()->assertSessionHas('status', 'Official list created successfully.');

    $list = MovieList::where('title', 'Space Features')->firstOrFail();
    $this->assertDatabaseHas('list_films', ['movie_list_id' => $list->id, 'film_id' => $scienceFictionFilm->id]);
    $this->get(route('admin.lists.show', $list))->assertOk()->assertSee('Nebula');
    $this->get(route('admin.lists.manage-films', $list))->assertOk()->assertSee('Manage Films')->assertSee('Nebula');

    $this->post(route('admin.lists.films.add', $list), ['film_ids' => [$scienceFictionFilm->id, $dramaFilm->id]])
        ->assertRedirect();
    $this->assertDatabaseCount('list_films', 2);
    $this->get(route('admin.lists.show', $list))->assertOk()->assertSee('Nebula')->assertSee('Quiet River');
    $this->delete(route('admin.lists.films.remove', [$list, $dramaFilm]))->assertRedirect();
    $this->assertDatabaseHas('films', ['id' => $dramaFilm->id]);
    $this->assertDatabaseMissing('list_films', ['movie_list_id' => $list->id, 'film_id' => $dramaFilm->id]);
});

test('editing and managing an official list synchronizes selected film relationships', function () {
    $admin = adminListUser();
    $first = Film::create(['title' => 'First Orbit', 'genre' => 'Science Fiction']);
    $second = Film::create(['title' => 'Second Orbit', 'genre' => 'Science Fiction']);
    $third = Film::create(['title' => 'Quiet Drama', 'genre' => 'Drama']);
    $list = MovieList::create([
        'user_id' => $admin->id,
        'title' => 'Orbit Collection',
        'is_public' => true,
        'is_official' => true,
        'classification_type' => 'genre',
        'classification' => 'Science Fiction',
    ]);
    $list->films()->attach([$first->id, $third->id]);

    $this->actingAs($admin)->get(route('admin.lists.edit', $list))
        ->assertOk()
        ->assertSee('First Orbit')
        ->assertSee('Quiet Drama')
        ->assertSee('adminListFilmPicker', false);

    $this->put(route('admin.lists.update', $list), [
        'title' => 'Orbit Collection Updated',
        'classification_type' => 'genre',
        'classification' => 'Science Fiction',
        'film_ids' => [$first->id, $second->id],
    ])->assertRedirect(route('admin.lists.show', $list));

    $this->assertDatabaseHas('list_films', ['movie_list_id' => $list->id, 'film_id' => $first->id]);
    $this->assertDatabaseHas('list_films', ['movie_list_id' => $list->id, 'film_id' => $second->id]);
    $this->assertDatabaseMissing('list_films', ['movie_list_id' => $list->id, 'film_id' => $third->id]);
    $this->assertDatabaseHas('films', ['id' => $third->id]);

    $this->post(route('admin.lists.films.sync', $list), ['film_ids' => [$third->id]])
        ->assertRedirect()
        ->assertSessionHas('status', 'Movie selection saved.');
    $this->assertDatabaseMissing('list_films', ['movie_list_id' => $list->id, 'film_id' => $first->id]);
    $this->assertDatabaseHas('list_films', ['movie_list_id' => $list->id, 'film_id' => $third->id]);
    $this->assertDatabaseHas('films', ['id' => $first->id]);
});

test('admin list filters use union matching and persist multiple genres and TMDB themes', function () {
    $admin = adminListUser();
    $scienceFiction = Film::create(['title' => 'Deep Space', 'genre' => 'Science Fiction']);
    $adventure = Film::create(['title' => 'Wild Journey', 'genre' => 'Adventure']);
    $both = Film::create(['title' => 'Star Quest', 'genre' => 'Science Fiction, Adventure']);
    $keyword = TmdbKeyword::create(['tmdb_id' => 4321001, 'name' => 'Time Travel']);
    $keyword->films()->attach([$scienceFiction->id, $both->id]);

    $this->actingAs($admin)->getJson(route('admin.lists.matching-films', [
        'classification_type' => 'genre',
        'classifications' => ['Science Fiction', 'Adventure'],
        'matching' => 1,
    ]))->assertOk()->assertJsonCount(3, 'films');

    $this->getJson(route('admin.lists.matching-films', [
        'classification_type' => 'theme',
        'classifications' => ['Time Travel'],
        'matching' => 1,
    ]))->assertOk()->assertJsonCount(2, 'films');

    $this->post(route('admin.lists.store'), [
        'title' => 'Unavailable Genre',
        'classification_type' => 'genre',
        'classifications' => ['Pirates'],
    ])->assertSessionHasErrors('classifications.0');
    $this->assertDatabaseMissing('movie_lists', ['title' => 'Unavailable Genre']);

    $this->post(route('admin.lists.store'), [
        'title' => 'Space and Adventure',
        'classification_type' => 'genre',
        'classifications' => ['Science Fiction', 'Adventure'],
        'film_ids' => [$scienceFiction->id, $both->id],
    ])->assertRedirect();

    $list = MovieList::where('title', 'Space and Adventure')->firstOrFail();
    expect($list->classifications)->toBe(['Science Fiction', 'Adventure']);
    $this->assertDatabaseCount('list_films', 2);

    $this->put(route('admin.lists.update', $list), [
        'title' => 'Space and Adventure Updated',
        'classification_type' => 'theme',
        'classifications' => ['Time Travel'],
        'film_ids' => [$adventure->id],
    ])->assertRedirect(route('admin.lists.show', $list));

    $list->refresh();
    expect($list->classifications)->toBe(['Time Travel']);
    $this->assertDatabaseHas('list_films', ['movie_list_id' => $list->id, 'film_id' => $adventure->id]);
    $this->assertDatabaseMissing('list_films', ['movie_list_id' => $list->id, 'film_id' => $scienceFiction->id]);
    $this->assertDatabaseHas('films', ['id' => $scienceFiction->id]);
});

test('theme suggestions come from existing official theme lists and films', function () {
    $admin = adminListUser();
    $film = Film::create(['title' => 'Orbiting Together', 'genre' => 'Science Fiction']);
    $existingTheme = MovieList::create([
        'user_id' => $admin->id,
        'title' => 'Existing Friendship Collection',
        'is_public' => true,
        'is_official' => true,
        'classification_type' => 'theme',
        'classification' => 'Friendship',
    ]);
    $existingTheme->films()->attach($film);

    $this->actingAs($admin)->get(route('admin.lists.create'))
        ->assertOk()
        ->assertSee('Friendship');

    $this->get(route('admin.lists.matching-films', [
        'classification_type' => 'theme',
        'classification' => 'Friendship',
        'matching' => 1,
    ]))->assertOk()->assertJsonPath('films.0.title', 'Orbiting Together');
});

test('admin movie picker searches local titles and TMDB IDs without requiring a classification', function () {
    $admin = adminListUser();
    $darkKnight = Film::create(['title' => 'The Dark Knight', 'original_title' => 'Batman: The Dark Knight', 'tmdb_id' => 155]);
    Film::create(['title' => 'Arrival', 'original_title' => 'Story of Your Life']);

    $response = $this->actingAs($admin)->getJson(route('admin.lists.matching-films', ['q' => 'batman']));
    $response->assertOk()
        ->assertJsonCount(1, 'films')
        ->assertJsonPath('films.0.id', $darkKnight->id)
        ->assertJsonPath('films.0.tmdb_id', 155)
        ->assertJsonPath('films.0.original_title', 'Batman: The Dark Knight');

    $this->getJson(route('admin.lists.matching-films', ['q' => '155']))
        ->assertOk()->assertJsonPath('films.0.title', 'The Dark Knight');
    $this->getJson(route('admin.lists.matching-films', ['q' => 'b']))->assertUnprocessable();
});

test('selecting a local film loads and stores cached TMDB genres and keywords for theme filtering', function () {
    Cache::flush();
    config(['services.tmdb.token' => 'test-token', 'services.tmdb.key' => null]);
    Http::fake(['api.themoviedb.org/3/movie/123*' => Http::response([
        'id' => 123,
        'genres' => [['id' => 878, 'name' => 'Science Fiction'], ['id' => 18, 'name' => 'Drama']],
        'keywords' => ['keywords' => [['id' => 987, 'name' => 'space exploration'], ['id' => 654, 'name' => 'time travel']]],
    ])]);
    $admin = adminListUser();
    $film = Film::create(['title' => 'Local Space Film', 'tmdb_id' => 123, 'genre' => 'Science Fiction']);

    $this->actingAs($admin)->getJson(route('admin.lists.films.metadata', $film))
        ->assertOk()
        ->assertJsonPath('available', true)
        ->assertJsonPath('genres.0.name', 'Science Fiction')
        ->assertJsonPath('keywords.0.name', 'space exploration');
    $this->assertDatabaseHas('tmdb_keywords', ['tmdb_id' => 987, 'name' => 'space exploration']);
    $this->assertDatabaseHas('film_tmdb_keyword', ['film_id' => $film->id]);

    $this->get(route('admin.lists.create'))->assertOk()->assertSee('space exploration');
    $this->getJson(route('admin.lists.matching-films', [
        'classification_type' => 'theme', 'classification' => 'space exploration', 'matching' => 1,
    ]))->assertOk()->assertJsonPath('films.0.title', 'Local Space Film');
    Http::assertSent(fn ($request) => str_contains($request->url(), '/movie/123') && $request['append_to_response'] === 'keywords');
});

test('admins can filter official lists and clients cannot manage them', function () {
    $admin = adminListUser();
    $client = User::factory()->create();
    $list = MovieList::create([
        'user_id' => $admin->id,
        'title' => 'Horror Night',
        'is_public' => true,
        'is_official' => true,
        'classification_type' => 'genre',
        'classification' => 'Horror',
    ]);

    $this->actingAs($admin)->get(route('admin.lists.index').'?type=genre&genre=Horror')
        ->assertOk()->assertSee('Horror Night');
    $this->get(route('admin.lists.index').'?type=theme&theme=Horror')
        ->assertOk()->assertDontSee('Horror Night');
    $this->actingAs($client)->get(route('lists.show', $list))->assertOk()->assertSee('Horror Night');
    $this->actingAs($client)->get(route('admin.lists.index'))->assertForbidden();
    $this->get(route('admin.lists.matching-films', ['classification_type' => 'genre', 'classification' => 'Horror']))->assertForbidden();
    $this->get(route('admin.lists.manage-films', $list))->assertForbidden();
    $this->actingAs($client)->delete(route('lists.destroy', $list))->assertForbidden();
    $this->actingAs($client)->post(route('lists.films.add', $list), ['film_id' => Film::create(['title' => 'Film'])->id])->assertForbidden();
});

test('admin list dashboard counts only official lists and supports allowlisted sorting', function () {
    $admin = adminListUser();
    $first = MovieList::create([
        'user_id' => $admin->id,
        'title' => 'Alpha Collection',
        'is_public' => true,
        'is_official' => true,
        'classification_type' => 'genre',
        'classification' => 'Drama',
    ]);
    $second = MovieList::create([
        'user_id' => $admin->id,
        'title' => 'Zulu Collection',
        'is_public' => true,
        'is_official' => true,
        'classification_type' => 'theme',
        'classification' => 'Friendship',
    ]);
    $personal = MovieList::create([
        'user_id' => $admin->id,
        'title' => 'Personal Collection',
        'is_public' => true,
        'is_official' => false,
    ]);
    $film = Film::create(['title' => 'List Film']);
    $second->films()->attach($film);

    $this->actingAs($admin)->get(route('admin.lists.index').'?sort=alphabetical')
        ->assertOk()
        ->assertSeeInOrder(['Alpha Collection', 'Zulu Collection'])
        ->assertSee('Total Movies in Lists')
        ->assertDontSee('Official Lists')
        ->assertDontSee('Personal Collection');

    $this->get(route('admin.lists.index').'?sort=most_films')
        ->assertOk()
        ->assertSeeInOrder(['Zulu Collection', 'Alpha Collection']);

    expect($personal->is_official)->toBeFalse();
});

test('deleting an official list removes its pivot rows, preserves films, and flashes a toast', function () {
    $admin = adminListUser();
    $film = Film::create(['title' => 'Still in Catalog']);
    $list = MovieList::create([
        'user_id' => $admin->id,
        'title' => 'Delete Me',
        'is_public' => true,
        'is_official' => true,
        'classification_type' => 'theme',
        'classification' => 'Temporary Theme',
    ]);
    $list->films()->attach($film);

    $this->actingAs($admin)->followingRedirects()
        ->delete(route('admin.lists.destroy', $list))
        ->assertOk()
        ->assertSee('List deleted successfully.');

    $this->assertDatabaseMissing('movie_lists', ['id' => $list->id]);
    $this->assertDatabaseMissing('list_films', ['movie_list_id' => $list->id, 'film_id' => $film->id]);
    $this->assertDatabaseHas('films', ['id' => $film->id, 'title' => 'Still in Catalog']);
});

test('admin list posters use public storage URLs and fail over to a placeholder', function () {
    Storage::fake('public');
    Storage::disk('public')->put('posters/orbit.jpg', 'poster-bytes');
    $admin = adminListUser();
    $list = MovieList::create([
        'user_id' => $admin->id,
        'title' => 'Poster Collection',
        'is_public' => true,
        'is_official' => true,
    ]);
    $storedPosterFilm = Film::create(['title' => 'Stored Poster', 'poster_path' => 'posters/orbit.jpg']);
    $availablePosterFilm = Film::create(['title' => 'Available Poster', 'poster_path' => 'posters/orbit.jpg']);
    $missingPosterFilm = Film::create(['title' => 'Missing Poster', 'poster_path' => 'posters/missing.jpg']);
    $legacyTmdbFilm = Film::create(['title' => 'Legacy TMDB Poster', 'poster_path' => '/legacy-poster.jpg']);
    Storage::disk('public')->put('posters/fourth.jpg', 'fourth-poster');
    $fourthFilm = Film::create(['title' => 'Fourth Poster', 'poster_path' => 'posters/fourth.jpg']);
    $list->films()->attach([$storedPosterFilm->id, $missingPosterFilm->id, $legacyTmdbFilm->id, $fourthFilm->id]);

    $response = $this->actingAs($admin)->get(route('admin.lists.show', $list));
    $response->assertOk()
        ->assertSee(Storage::disk('public')->url('posters/orbit.jpg'), false)
        ->assertSee('https://image.tmdb.org/t/p/w500/legacy-poster.jpg', false)
        ->assertSee('onerror=', false)
        ->assertSee('No Poster')
        ->assertDontSee('posters/missing.jpg');

    $this->get(route('admin.films.index'))
        ->assertOk()
        ->assertSee(Storage::disk('public')->url('posters/orbit.jpg'), false)
        ->assertSee('onerror=', false);

    $this->get(route('admin.lists.index'))
        ->assertOk()
        ->assertSee(Storage::disk('public')->url('posters/orbit.jpg'), false)
        ->assertSee(Storage::disk('public')->url('posters/fourth.jpg'), false)
        ->assertSee('Create Official List')
        ->assertSee('Total Lists')
        ->assertSee('Total Movies in Lists')
        ->assertSee('href="'.route('admin.lists.create').'"', false)
        ->assertSee('group/list-poster relative h-36 w-60', false)
        ->assertSee('object-cover', false)
        ->assertSee('class="h-full w-full relative overflow-hidden', false)
        ->assertDontSee('create-official-list-drawer');

    $this->get(route('admin.lists.matching-films', ['list_id' => $list->id, 'q' => 'Available']))
        ->assertOk()
        ->assertJsonPath('films.0.title', 'Available Poster')
        ->assertJsonPath('films.0.poster_url', Storage::disk('public')->url('posters/orbit.jpg'));

    $this->get(route('admin.lists.show', $list))->assertOk()->assertSee('aspect-[2/3]', false);
});
