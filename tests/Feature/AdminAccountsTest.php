<?php

use App\Models\Film;
use App\Models\MovieList;
use App\Models\Review;
use App\Models\ReviewReaction;
use App\Models\User;
use Spatie\Permission\Models\Role;

function createAccountAdmin(): User
{
    Role::findOrCreate('admin');
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    return $admin;
}

test('only admins can access the accounts directory and search and filters use real accounts', function () {
    $admin = createAccountAdmin();
    $active = User::factory()->create(['name' => 'Active Reviewer', 'email' => 'active@example.test']);
    $suspended = User::factory()->create(['name' => 'Suspended Reviewer', 'status' => 'suspended']);
    $suspended->assignRole(Role::findOrCreate('client'));
    $film = Film::create(['title' => 'Account Film']);
    Review::create(['user_id' => $active->id, 'film_id' => $film->id, 'rating' => 5, 'comment' => 'Great']);

    $this->actingAs($active)->get(route('admin.accounts.index'))->assertForbidden();
    $this->actingAs($active)->patch(route('admin.accounts.suspend', $suspended))->assertForbidden();
    $this->actingAs($active)->delete(route('admin.accounts.destroy', $suspended))->assertForbidden();

    $this->actingAs($admin)->get(route('admin.accounts.index', ['q' => 'active@example.test', 'status' => 'active', 'role' => 'client']))
        ->assertOk()
        ->assertSee('Accounts')
        ->assertSee('Active Reviewer')
        ->assertSee('Total Members')
        ->assertSee('data-confirm-title="Suspend this account?"', false)
        ->assertSee('data-confirm-title="Delete this account?"', false)
        ->assertDontSee('Suspended Reviewer');

    $this->actingAs($admin)->get(route('admin.accounts.index', ['role' => 'admin']))
        ->assertOk()
        ->assertSee($admin->email)
        ->assertDontSee($active->email);
});

test('accounts directory paginates while preserving filters', function () {
    $admin = createAccountAdmin();
    User::factory()->count(16)->create();

    $response = $this->actingAs($admin)->get(route('admin.accounts.index', ['role' => 'client', 'page' => 2]));

    $response->assertOk()->assertSee('Previous')->assertSee('role=client', false);
});

test('admin navigation uses Accounts while client navigation keeps Members', function () {
    $admin = createAccountAdmin();

    $this->actingAs($admin)->get(route('admin.accounts.index'))
        ->assertOk()
        ->assertSee('Accounts')
        ->assertDontSee('Add Film')
        ->assertSee('Lists')
        ->assertDontSee('>Members</a>', false);

    $this->get(route('members.index'))
        ->assertOk()
        ->assertSee('Members');

    $this->get(route('admin.films.index'))->assertOk()->assertSee('Add Film');
});

test('admin account profile shows recorded activity and keeps private list content hidden', function () {
    $admin = createAccountAdmin();
    $member = User::factory()->create(['name' => 'Account Member']);
    $reactor = User::factory()->create();
    $reviewedFilm = Film::create(['title' => 'Reviewed Film']);
    $favoriteFilm = Film::create(['title' => 'Favorite Film']);
    $privateFilm = Film::create(['title' => 'Private List Film']);
    $review = Review::create(['user_id' => $member->id, 'film_id' => $reviewedFilm->id, 'rating' => 4, 'comment' => 'Member review comment']);

    ReviewReaction::create(['user_id' => $reactor->id, 'review_id' => $review->id, 'reaction' => 'agree']);
    $member->favoriteFilms()->attach($favoriteFilm->id);

    $publicList = MovieList::create(['user_id' => $member->id, 'title' => 'Public Collection', 'is_public' => true]);
    $publicList->films()->attach($reviewedFilm->id);
    $privateList = MovieList::create(['user_id' => $member->id, 'title' => 'Private Collection', 'description' => 'Private description that must not leak', 'is_public' => false]);
    $privateList->films()->attach($privateFilm->id);

    $response = $this->actingAs($admin)->get(route('admin.accounts.show', ['user' => $member, 'tab' => 'activity']));

    $response->assertOk()
        ->assertSee('Overview')
        ->assertSee('Activity')
        ->assertSee('Reviews')
        ->assertSee('Public Lists')
        ->assertSee('Member review comment')
        ->assertSee('Favorite Film')
        ->assertSee('Public Collection')
        ->assertSee('Received an')
        ->assertDontSee('Private Collection')
        ->assertDontSee('Private description that must not leak')
        ->assertDontSee('Private List Film');
});

test('account moderation suspends and restores clients, and deletion reports success', function () {
    $admin = createAccountAdmin();
    $member = User::factory()->create(['status' => 'active']);

    $this->actingAs($admin)
        ->from(route('admin.accounts.index'))
        ->patch(route('admin.accounts.suspend', $member))
        ->assertRedirect(route('admin.accounts.index'))
        ->assertSessionHas('status', 'Account suspended successfully.');
    expect($member->fresh()->status)->toBe('suspended');

    $this->actingAs($admin)
        ->patch(route('admin.accounts.reactivate', $member))
        ->assertRedirect()
        ->assertSessionHas('status', 'Account unsuspended successfully.');
    expect($member->fresh()->status)->toBe('active');

    $this->actingAs($admin)
        ->delete(route('admin.accounts.destroy', $member))
        ->assertRedirect(route('admin.accounts.index'))
        ->assertSessionHas('status', 'Account deleted successfully.');
    $this->assertDatabaseMissing('users', ['id' => $member->id]);
});
