<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

it('admin can look up a movie from tmdb for autofill', function () {
    Role::firstOrCreate(['name' => 'admin']);

    $user = User::factory()->create();
    $user->assignRole('admin');

    $response = $this->actingAs($user)->getJson('/admin/films/tmdb-search?q=Inception');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'title',
            'synopsis',
            'genre',
            'release_date',
            'release_year',
            'cast',
            'poster_url',
        ]);
});
