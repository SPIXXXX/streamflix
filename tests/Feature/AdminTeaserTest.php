<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

test('admin can open the teaser create form', function () {
    Role::firstOrCreate(['name' => 'admin']);

    $user = User::factory()->create();
    $user->assignRole('admin');

    $response = $this->actingAs($user)->get('/admin/teasers/create');

    $response->assertOk()
        ->assertSee('Add Teaser');
});
