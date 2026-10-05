<?php

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

beforeEach(function () {
    $this->withoutMiddleware(PreventRequestForgery::class);
});

test('guests cannot send a heartbeat', function () {
    $this->postJson(route('heartbeat'))->assertUnauthorized();
});

test('heartbeat updates only the authenticated user', function () {
    $this->travelTo(now()->startOfSecond());
    $user = User::factory()->create(['last_seen_at' => null]);
    $otherUser = User::factory()->create(['last_seen_at' => null]);

    $this->actingAs($user)
        ->postJson(route('heartbeat'), ['user_id' => $otherUser->id])
        ->assertOk()
        ->assertExactJson(['success' => true]);

    expect($user->fresh()->last_seen_at->equalTo(now()))->toBeTrue()
        ->and($otherUser->fresh()->last_seen_at)->toBeNull();
});

test('clients can refresh activity for active public members only', function () {
    $client = User::factory()->create();
    $member = User::factory()->create(['last_seen_at' => now()]);
    $suspendedMember = User::factory()->create(['status' => 'suspended', 'last_seen_at' => now()]);

    $this->actingAs($client)
        ->getJson(route('user-activity.status', ['users' => [$member->id, $suspendedMember->id]]))
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $member->id)
        ->assertJsonPath('0.online', true)
        ->assertJsonPath('0.label', 'Online');
});

test('activity status uses the two minute online window and handles never active users', function () {
    $this->travelTo(now()->startOfSecond());

    $onlineNow = User::factory()->create(['last_seen_at' => now()]);
    $onlineMinuteAgo = User::factory()->create(['last_seen_at' => now()->subMinute()]);
    $offline = User::factory()->create(['last_seen_at' => now()->subMinutes(3)]);
    $neverActive = User::factory()->create(['last_seen_at' => null]);

    expect($onlineNow->is_online)->toBeTrue()
        ->and($onlineMinuteAgo->is_online)->toBeTrue()
        ->and($offline->is_online)->toBeFalse()
        ->and($offline->activity_status)->toBe('Active 3 minutes ago')
        ->and($neverActive->is_online)->toBeFalse()
        ->and($neverActive->activity_status)->toBe('Never active');
});
