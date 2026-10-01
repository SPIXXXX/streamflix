<?php

use App\Models\Film;
use App\Models\MovieList;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

test('a user can toggle a film in favorites and see it on the lists page', function () {
    $user = User::factory()->create();
    $film = Film::create(['title' => 'Favorite Test Film']);

    $this->actingAs($user)
        ->postJson(route('films.favorite', $film))
        ->assertOk()
        ->assertJson(['is_favorite' => true]);

    expect($user->favoriteFilms()->whereKey($film->id)->exists())->toBeTrue();
    $this->assertDatabaseHas('film_favorites', [
        'user_id' => $user->id,
        'film_id' => $film->id,
    ]);

    $this->get(route('lists.index'))
        ->assertOk()
        ->assertSee('Favorite Test Film')
        ->assertSee('1 movie');

    $this->get(route('lists.favorites'))
        ->assertOk()
        ->assertSee('Favorite Test Film')
        ->assertSee('Remove Favorite Test Film from favorites');

    $this->get(route('films.show', $film))
        ->assertOk()
        ->assertSee('Favorited')
        ->assertDontSee('View Favorites');

    $this->postJson(route('films.favorite', $film))
        ->assertOk()
        ->assertJson(['is_favorite' => false]);

    expect($user->favoriteFilms()->whereKey($film->id)->exists())->toBeFalse();
    $this->assertDatabaseMissing('film_favorites', [
        'user_id' => $user->id,
        'film_id' => $film->id,
    ]);
    $this->assertDatabaseHas('films', ['id' => $film->id]);
});

test('a guest cannot favorite a film', function () {
    $film = Film::create(['title' => 'Login Required Film']);

    $this->post(route('films.favorite', $film))
        ->assertRedirect(route('login'));

    $this->assertDatabaseCount('movie_lists', 0);
});

test('the lists page only shows the authenticated user favorites', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $usersFilm = Film::create(['title' => 'User A Favorite']);
    $otherUsersFilm = Film::create(['title' => 'User B Private Favorite']);

    $userA->favoriteFilms()->attach($usersFilm);
    $userB->favoriteFilms()->attach($usersFilm);
    $userB->favoriteFilms()->attach($otherUsersFilm);

    $this->actingAs($userA)
        ->get(route('lists.index'))
        ->assertOk()
        ->assertSee('User A Favorite')
        ->assertDontSee('User B Private Favorite');

    $this->actingAs($userA)->postJson(route('films.favorite', $usersFilm))->assertJson(['is_favorite' => false]);

    expect($userB->favoriteFilms()->whereKey($usersFilm->id)->exists())->toBeTrue();
    $this->assertDatabaseHas('films', ['id' => $usersFilm->id]);
});

test('a user list appears as a card and its owner can manage its movies', function () {
    $owner = User::factory()->create();
    $film = Film::create([
        'title' => 'Watchlist Test Film',
        'release_year' => 2025,
        'poster_path' => null,
    ]);

    $this->actingAs($owner)
        ->post(route('lists.store'), [
            'title' => 'Private Watchlist',
            'description' => 'Films to watch next.',
            'is_public' => false,
        ])
        ->assertRedirect();

    $list = $owner->movieLists()->where('title', 'Private Watchlist')->firstOrFail();

    $this->get(route('lists.index'))
        ->assertOk()
        ->assertSee('Private Watchlist')
        ->assertSee('0 movies');

    $this->get(route('lists.show', $list))->assertOk();

    $this->post(route('lists.films.add', $list), ['film_id' => $film->id])
        ->assertRedirect();

    $this->get(route('lists.show', $list))
        ->assertOk()
        ->assertSee('Watchlist Test Film')
        ->assertSee('1 movie');

    $otherUser = User::factory()->create();
    $this->actingAs($otherUser)->get(route('lists.show', $list))->assertForbidden();
    $this->put(route('lists.update', $list), ['title' => 'Stolen List'])->assertForbidden();
    $this->delete(route('lists.destroy', $list))->assertForbidden();
    $this->post(route('lists.films.add', $list), ['film_id' => $film->id])->assertForbidden();
    $this->delete(route('lists.films.remove', [$list, $film]))->assertForbidden();

    $this->actingAs($owner)
        ->put(route('lists.update', $list), [
            'title' => 'Updated Watchlist',
            'description' => 'Updated collection.',
            'is_public' => false,
        ])
        ->assertRedirect();

    $list->refresh();
    $this->get(route('lists.index'))->assertSee('Updated Watchlist');

    $this->delete(route('lists.films.remove', [$list, $film]))->assertRedirect();
    $this->get(route('lists.show', $list))
        ->assertOk()
        ->assertDontSee(route('films.show', $film))
        ->assertSee("This list doesn't have any movies yet.");
    $this->assertDatabaseHas('films', ['id' => $film->id]);

    $this->delete(route('lists.destroy', $list))->assertRedirect(route('lists.index'));
    $this->assertDatabaseMissing('movie_lists', ['id' => $list->id]);
});

test('featured and crew sections use official public list data', function () {
    Role::firstOrCreate(['name' => 'admin']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $owner = User::factory()->create();
    Film::create(['title' => 'Science Fiction Seed', 'genre' => 'Science Fiction']);

    $this->actingAs($admin)
        ->post(route('admin.lists.store'), [
            'title' => 'Admin Featured Collection',
            'description' => 'A curated featured collection.',
            'classification_type' => 'genre',
            'classification' => 'Science Fiction',
            'is_featured' => true,
        ])
        ->assertRedirect(route('admin.lists.show', MovieList::where('title', 'Admin Featured Collection')->firstOrFail()));

    $this->assertDatabaseHas('movie_lists', [
        'title' => 'Admin Featured Collection',
        'is_official' => true,
        'is_public' => true,
        'is_featured' => true,
    ]);

    MovieList::create([
        'user_id' => $admin->id,
        'title' => 'Official Crew Collection',
        'is_public' => true,
        'is_official' => true,
        'is_featured' => false,
    ]);
    MovieList::create([
        'user_id' => $owner->id,
        'title' => 'Private Personal Collection',
        'is_public' => false,
        'is_official' => false,
    ]);

    $response = $this->actingAs($owner)->get(route('lists.index'))->assertOk();
    $content = $response->getContent();
    $featuredSection = Str::between($content, 'Featured Lists', 'Recently Popular');
    $crewSection = Str::between($content, 'Crew Picks', 'Explore Public Lists');

    expect($featuredSection)->toContain('Admin Featured Collection')
        ->not->toContain('Official Crew Collection')
        ->not->toContain('Private Personal Collection');
    expect($crewSection)->toContain('Official Crew Collection')
        ->not->toContain('Admin Featured Collection')
        ->not->toContain('Private Personal Collection');
});

test('recently popular lists use recent activity and real film counts', function () {
    $owner = User::factory()->create();
    $recentList = MovieList::create([
        'user_id' => $owner->id,
        'title' => 'Recently Updated Collection',
        'is_public' => true,
        'is_official' => false,
    ]);
    $staleList = MovieList::create([
        'user_id' => $owner->id,
        'title' => 'Old Collection',
        'is_public' => true,
        'is_official' => false,
    ]);
    DB::table('movie_lists')->where('id', $staleList->id)->update([
        'created_at' => now()->subDays(40),
        'updated_at' => now()->subDays(31),
    ]);
    $films = collect(range(1, 3))->map(fn (int $number) => Film::create([
        'title' => "Popular Film {$number}",
    ]));
    $recentList->films()->attach($films->pluck('id')->all());

    $content = $this->get(route('lists.index'))->assertOk()->getContent();
    $popularSection = Str::between($content, 'Recently Popular', 'Crew Picks');

    expect($popularSection)
        ->toContain('Recently Updated Collection')
        ->toContain('3 movies')
        ->not->toContain('Old Collection');
    $this->assertDatabaseHas('movie_lists', ['id' => $staleList->id]);
});

test('admins can manage official lists without using admin list routes on personal lists', function () {
    Role::firstOrCreate(['name' => 'admin']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $owner = User::factory()->create();
    $personalList = $owner->movieLists()->create([
        'title' => 'Personal Collection',
        'is_public' => true,
    ]);
    $officialList = MovieList::create([
        'user_id' => $admin->id,
        'title' => 'Official Collection',
        'is_public' => true,
        'is_official' => true,
        'is_featured' => false,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.lists.update', $officialList), [
            'title' => 'Featured Official Collection',
            'description' => 'Selected by the crew.',
            'classification_type' => 'theme',
            'classification' => 'Crew Picks',
            'is_featured' => true,
        ])
        ->assertRedirect(route('admin.lists.show', $officialList));

    $this->assertDatabaseHas('movie_lists', [
        'id' => $officialList->id,
        'is_featured' => true,
    ]);

    $this->get(route('admin.lists.edit', $personalList))->assertNotFound();
    $this->put(route('admin.lists.update', $personalList), ['title' => 'Changed'])->assertNotFound();
    $this->delete(route('admin.lists.destroy', $personalList))->assertNotFound();
    $this->assertDatabaseHas('movie_lists', ['id' => $personalList->id]);
});

test('public lists are viewable by guests while private lists remain owner only', function () {
    $owner = User::factory()->create();
    $publicList = $owner->movieLists()->create([
        'title' => 'Public Community List',
        'is_public' => true,
    ]);
    $privateList = $owner->movieLists()->create([
        'title' => 'Private Community List',
        'is_public' => false,
    ]);

    $this->get(route('lists.show', $publicList))
        ->assertOk()
        ->assertSee('Public Community List');
    $this->get(route('lists.show', $privateList))->assertForbidden();
});
