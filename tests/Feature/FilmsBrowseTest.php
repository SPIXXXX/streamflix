<?php

use App\Models\Film;
use App\Models\MovieList;
use App\Models\Review;
use App\Models\ReviewReaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

test('films shelves use the stored release date and show clean empty states', function () {
    $olderRelease = Film::create([
        'title' => 'Older Release',
        'release_year' => 2025,
        'release_date' => '2025-03-01',
    ]);
    $newerRelease = Film::create([
        'title' => 'Newer Release',
        'release_year' => 2024,
        'release_date' => '2024-01-01',
    ]);
    DB::table('films')->where('id', $olderRelease->id)->update(['created_at' => now()->subYear()]);
    DB::table('films')->where('id', $newerRelease->id)->update(['created_at' => now()]);
    Film::create(['title' => 'No Release Date']);

    $response = $this->get(route('films.index'));

    $response->assertOk()
        ->assertSeeInOrder(['Popular Reviews This Week', 'Recently Added', 'Older Release', 'Newer Release'])
        ->assertSee('No popular movies are available yet.')
        ->assertSee(route('films.collections', 'popular-reviews-this-week'))
        ->assertSee(route('films.collections', 'recently-added'))
        ->assertSee(route('films.collections', 'popular-this-week'))
        ->assertSee(route('films.collections', 'popular-movies'))
        ->assertSee(route('films.collections', 'highest-rated'))
        ->assertSee(route('films.collections', 'all'))
        ->assertSee(route('films.show', $olderRelease))
        ->assertSee(route('films.show', $newerRelease));
});

test('film collection pages paginate and keep using the poster details route', function () {
    foreach (range(1, 25) as $index) {
        Film::create([
            'title' => "Release Collection Film {$index}",
            'release_date' => now()->subDays($index)->toDateString(),
        ]);
    }

    $this->get(route('films.collections', ['category' => 'recently-added', 'page' => 2]))
        ->assertOk()
        ->assertSee('Release Collection Film 25')
        ->assertSee(route('films.show', Film::where('title', 'Release Collection Film 25')->first()));

    $this->get(route('films.collections', 'unknown-category'))->assertNotFound();
});

test('popular reviews view all paginates the same recent review cards', function () {
    $author = User::factory()->create();
    foreach (range(1, 25) as $index) {
        $film = Film::create(['title' => "Review Collection Film {$index}"]);
        Review::create([
            'user_id' => $author->id,
            'film_id' => $film->id,
            'rating' => 4,
            'comment' => "Recent collection review {$index}.",
        ]);
    }

    $this->get(route('films.collections', ['category' => 'popular-reviews-this-week', 'page' => 2]))
        ->assertOk()
        ->assertSee('Popular Reviews This Week')
        ->assertSee('Recent collection review 1.')
        ->assertSee('4/5');
});

test('admin film cards use the shared poster design and keep edit and delete controls', function () {
    Role::firstOrCreate(['name' => 'admin']);
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $film = Film::create(['title' => 'Managed Poster Film']);

    $this->actingAs($admin)
        ->get(route('admin.films.index'))
        ->assertOk()
        ->assertSee(route('admin.films.show', $film))
        ->assertSee(route('admin.films.edit', $film))
        ->assertSee(route('admin.films.destroy', $film));

    $this->get(route('admin.films.edit', $film))->assertOk();
    $this->delete(route('admin.films.destroy', $film))->assertRedirect();
    $this->assertDatabaseMissing('films', ['id' => $film->id]);
});

test('popular film shelves rank actual site activity and search finds catalogue films', function () {
    $activeFilm = Film::create(['title' => 'Active Film', 'release_date' => '2020-01-01']);
    $quietFilm = Film::create(['title' => 'Quiet Film', 'release_date' => '2021-01-01']);
    $member = User::factory()->create();
    $review = Review::create([
        'user_id' => $member->id,
        'film_id' => $activeFilm->id,
        'rating' => 4,
        'comment' => 'A very good film.',
    ]);
    $member->favoriteFilms()->attach($activeFilm->id);
    $personalList = MovieList::create([
        'user_id' => $member->id,
        'title' => 'Weekend Watchlist',
        'is_public' => true,
        'is_official' => false,
    ]);
    $personalList->films()->attach($activeFilm->id);

    $response = $this->get(route('films.index'));
    $popularShelf = Str::before(
        Str::after($response->getContent(), 'id="popular-films-title"'),
        'id="highest-rated-title"'
    );
    $exploreShelf = Str::after($response->getContent(), 'id="explore-films-title"');

    $response->assertOk();
    expect($popularShelf)->toContain('Active Film')->not->toContain('Quiet Film');
    expect($exploreShelf)->toContain('Quiet Film');

    $this->get(route('films.index', ['q' => 'Quiet']))
        ->assertOk()
        ->assertSee('Search results')
        ->assertSee('Quiet Film')
        ->assertDontSee('Active Film');
});

test('popular reviews are ranked by all reactions from the last seven days', function () {
    $film = Film::create(['title' => 'Reaction Film']);
    $author = User::factory()->create();
    $recentVoter = User::factory()->create();
    $oldVoter = User::factory()->create();
    $recentReview = Review::create([
        'user_id' => $author->id,
        'film_id' => $film->id,
        'rating' => 5,
        'comment' => 'Recent reaction review.',
    ]);
    $oldReview = Review::create([
        'user_id' => $oldVoter->id,
        'film_id' => $film->id,
        'rating' => 4,
        'comment' => 'Older reaction review.',
    ]);
    DB::table('reviews')->where('id', $oldReview->id)->update(['created_at' => now()->subMonths(2)]);
    ReviewReaction::create([
        'user_id' => $recentVoter->id,
        'review_id' => $recentReview->id,
        'reaction' => 'agree',
        'created_at' => now()->subDays(2),
        'updated_at' => now()->subDays(2),
    ]);
    ReviewReaction::create([
        'user_id' => User::factory()->create()->id,
        'review_id' => $recentReview->id,
        'reaction' => 'disagree',
    ]);
    ReviewReaction::create([
        'user_id' => $oldVoter->id,
        'review_id' => $oldReview->id,
        'reaction' => 'agree',
    ])->forceFill(['created_at' => now()->subDays(8), 'updated_at' => now()->subDays(8)])->save();

    $this->get(route('films.index'))
        ->assertOk()
        ->assertSee('Reaction Film')
        ->assertSee('Recent reaction review.')
        ->assertSee('1 agrees this week');
});

test('popular this week excludes activity older than seven days', function () {
    $recentFilm = Film::create(['title' => 'Recently Active Film']);
    $olderFilm = Film::create(['title' => 'Previously Active Film']);
    $member = User::factory()->create();
    Review::create([
        'user_id' => $member->id,
        'film_id' => $recentFilm->id,
        'rating' => 5,
    ]);
    $olderReview = Review::create([
        'user_id' => User::factory()->create()->id,
        'film_id' => $olderFilm->id,
        'rating' => 5,
    ]);
    DB::table('reviews')->where('id', $olderReview->id)->update(['created_at' => now()->subDays(8)]);

    $response = $this->get(route('films.index'));
    $weeklyShelf = Str::before(
        Str::after($response->getContent(), 'id="popular-this-week-title"'),
        'id="popular-films-title"'
    );

    expect($weeklyShelf)->toContain('Recently Active Film')->not->toContain('Previously Active Film');
});

test('highest rated shelf uses member ratings and requires two ratings', function () {
    $highRatedFilm = Film::create(['title' => 'Community Favorite']);
    $singleRatingFilm = Film::create(['title' => 'Single Rating Five']);
    foreach ([5, 4] as $rating) {
        Review::create([
            'user_id' => User::factory()->create()->id,
            'film_id' => $highRatedFilm->id,
            'rating' => $rating,
        ]);
    }
    Review::create([
        'user_id' => User::factory()->create()->id,
        'film_id' => $singleRatingFilm->id,
        'rating' => 5,
    ]);

    $response = $this->get(route('films.index'));
    $highestRatedShelf = Str::before(
        Str::after($response->getContent(), 'id="highest-rated-title"'),
        'id="explore-films-title"'
    );

    expect($highestRatedShelf)->toContain('Community Favorite')->not->toContain('Single Rating Five');
});

test('a member can submit a film rating and review', function () {
    $member = User::factory()->create();
    $film = Film::create(['title' => 'Reviewable Film']);

    $this->actingAs($member)
        ->post(route('films.reviews.store', $film), [
            'rating' => 5,
            'comment' => 'A memorable film.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('reviews', [
        'film_id' => $film->id,
        'user_id' => $member->id,
        'rating' => 5,
        'comment' => 'A memorable film.',
    ]);
});
