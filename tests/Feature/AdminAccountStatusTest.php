<?php

use App\Models\User;

test('suspended users cannot authenticate', function () {
    $user = User::factory()->create();
    $user->forceFill(['status' => 'suspended'])->save();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});
