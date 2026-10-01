<?php

use App\Models\Film;
use App\Models\MovieList;
use App\Models\Review;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('member discovery uses real review activity and curated featured status', function () {
    $activeReviewer = User::factory()->create(['name' => 'Active Reviewer']);
    $featuredMember = User::factory()->create(['name' => 'Featured Member', 'is_featured' => true]);
    $privateListOnlyMember = User::factory()->create(['name' => 'Private List Member']);
    $film = Film::create(['title' => 'Example Film']);

    Review::create(['user_id' => $activeReviewer->id, 'film_id' => $film->id, 'rating' => 5, 'comment' => 'Loved it.']);
    MovieList::create(['user_id' => $privateListOnlyMember->id, 'title' => 'Private picks', 'is_public' => false]);

    $response = $this->get(route('members.index'));

    $response->assertOk()
        ->assertSee('Most Active Reviewers')
        ->assertSee('Featured Members')
        ->assertSee('Popular Members')
        ->assertSee('All Members')
        ->assertSee('Active Reviewer')
        ->assertSee('Featured Member')
        ->assertSee('Private List Member');
});

test('member profile exposes public reviews and lists but never private lists', function () {
    $member = User::factory()->create(['name' => 'Public Member']);
    $viewer = User::factory()->create();
    $film = Film::create(['title' => 'Public Film']);
    Review::create(['user_id' => $member->id, 'film_id' => $film->id, 'rating' => 4, 'comment' => 'A public review.']);
    MovieList::create(['user_id' => $member->id, 'title' => 'Shared Picks', 'is_public' => true]);
    MovieList::create(['user_id' => $member->id, 'title' => 'Secret Picks', 'is_public' => false]);

    $response = $this->actingAs($viewer)->get(route('members.show', $member));

    $response->assertOk()
        ->assertSee('Public Member')
        ->assertSee('Overview')
        ->assertSee('Reviews')
        ->assertSee('Public Lists')
        ->assertSee('Public Film')
        ->assertSee('Shared Picks')
        ->assertDontSee('Secret Picks')
        ->assertSee('A public review.');

    $reviewsResponse = $this->get(route('members.show', ['user' => $member, 'tab' => 'reviews']));
    $reviewsResponse->assertOk()->assertSee('A public review.');

    $listsResponse = $this->get(route('members.show', ['user' => $member, 'tab' => 'lists']));
    $listsResponse->assertOk()->assertSee('Shared Picks')->assertDontSee('Secret Picks');
});

test('admins can curate featured members while the existing personal profile remains available', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin'));
    $member = User::factory()->create(['is_featured' => false]);

    $this->actingAs($admin)
        ->patch(route('admin.accounts.feature', $member), ['is_featured' => true])
        ->assertRedirect();

    expect($member->fresh()->is_featured)->toBeTrue();

    $this->actingAs($member)->get(route('profile.edit'))->assertOk();
});
