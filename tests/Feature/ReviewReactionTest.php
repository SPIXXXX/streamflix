<?php

use App\Models\Film;
use App\Models\Review;
use App\Models\User;

test('user can agree, toggle and switch reaction', function () {
    $user = User::factory()->create();

    $film = Film::create(['title' => 'Test Movie']);

    $review = Review::create([
        'user_id' => $user->id,
        'film_id' => $film->id,
        'rating' => 4,
        'comment' => 'Nice movie',
    ]);

    // Agree
    $this->actingAs($user)
        ->postJson(route('reviews.reactions.store', $review), ['reaction' => 'agree'])
        ->assertOk()
        ->assertJson(['agree' => 1, 'disagree' => 0]);

    // Toggling agree removes it
    $this->actingAs($user)
        ->postJson(route('reviews.reactions.store', $review), ['reaction' => 'agree'])
        ->assertOk()
        ->assertJson(['agree' => 0, 'disagree' => 0]);

    // Switch to disagree
    $this->actingAs($user)
        ->postJson(route('reviews.reactions.store', $review), ['reaction' => 'disagree'])
        ->assertOk()
        ->assertJson(['agree' => 0, 'disagree' => 1]);
});
