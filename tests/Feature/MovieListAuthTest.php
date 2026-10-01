<?php

use App\Models\Film;
use App\Models\MovieList;
use App\Models\User;

test('guest can browse lists without seeing a personal favorites section', function () {
    $response = $this->get('/lists');

    $response->assertOk()
        ->assertSee('Movie Bucket List')
        ->assertDontSee('favorite-films-heading');
});

test('client list cards use stable attached movie previews and handle empty through large lists', function () {
    $user = User::factory()->create();
    $films = collect(range(1, 10))->map(fn (int $number) => Film::create([
        'title' => 'Collection Film '.$number,
        'poster_path' => '/collection-'.$number.'.jpg',
    ]));

    foreach (range(0, 10) as $movieCount) {
        $list = $user->movieLists()->create([
            'title' => 'Collection '.$movieCount,
            'description' => 'A stable preview test.',
            'is_public' => $movieCount % 2 === 0,
        ]);
        if ($movieCount > 0) {
            $list->films()->attach($films->take($movieCount)->pluck('id')->all());
        }
    }

    $featured = MovieList::create([
        'user_id' => $user->id,
        'title' => 'Client Featured Picks',
        'description' => 'Featured test collection.',
        'is_public' => true,
        'is_official' => true,
        'is_featured' => true,
    ]);
    $featured->films()->attach($films->take(3)->pluck('id')->all());

    $crewPick = MovieList::create([
        'user_id' => $user->id,
        'title' => 'Client Crew Picks',
        'is_public' => true,
        'is_official' => true,
    ]);
    $crewPick->films()->attach($films->take(2)->pluck('id')->all());

    $user->favoriteFilms()->attach($films->take(10)->pluck('id')->all());

    $response = $this->actingAs($user)->get(route('lists.index'));

    $response->assertOk()
        ->assertSee('Collection 0')
        ->assertSee('Collection 1')
        ->assertSee('Collection 10')
        ->assertSee('Client Featured Picks')
        ->assertSee('Client Crew Picks')
        ->assertSee('Featured Lists')
        ->assertSee('Recently Popular')
        ->assertSee('Crew Picks')
        ->assertSee('No movies yet')
        ->assertSee('Collection Film 10 poster')
        ->assertSee('Collection Film 7 poster')
        ->assertSee('+7')
        ->assertSee('+6')
        ->assertSee('Public')
        ->assertSee('Private')
        ->assertSee('Favorites')
        ->assertSee(route('lists.favorites'));
});
