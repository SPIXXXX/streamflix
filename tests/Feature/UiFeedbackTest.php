<?php

use App\Models\Film;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('client layout renders the reusable confirmation dialog and flash toast', function () {
    $response = $this->withSession(['status' => 'Review posted!'])->get(route('films.index'));

    $response->assertOk()
        ->assertSee('id="site-confirmation-dialog"', false)
        ->assertSee('id="site-confirmation-title"', false)
        ->assertSee('Cancel')
        ->assertSee('data-toast-container', false)
        ->assertSee('Review posted!');
});

test('admin film delete uses the shared confirmation dialog and preserves delete form fields', function () {
    Role::firstOrCreate(['name' => 'admin']);
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $film = Film::create(['title' => 'Confirmable Film']);

    $this->actingAs($admin)
        ->get(route('admin.films.index'))
        ->assertOk()
        ->assertSee('id="site-confirmation-dialog"', false)
        ->assertSee('data-toast-container', false)
        ->assertSee('data-confirm-title="Delete this film?"', false)
        ->assertSee('Confirmable Film will be permanently removed.', false)
        ->assertSee(route('admin.films.destroy', $film))
        ->assertSee('name="_method" value="DELETE"', false)
        ->assertSee('name="_token"', false);
});

test('error flash messages render as accessible error toasts', function () {
    $this->withSession(['error' => 'Unable to complete this operation.'])
        ->get(route('films.index'))
        ->assertOk()
        ->assertSee('role="alert"', false)
        ->assertSee('Unable to complete this operation.');
});
