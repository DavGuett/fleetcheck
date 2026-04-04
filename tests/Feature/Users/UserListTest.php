
<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('user list page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('users.index'));

    $response->assertOk();
});

test('user list contains users as an array', function () {
    $user = User::factory()->create(['name' => 'John Doe']);
    User::factory()->create(['name' => 'Jane Smith']);

    $response = $this
        ->actingAs($user)
        ->get(route('users.index'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('users/UserList')
        ->has('users', 2)
        ->where('users.0.name', 'Jane Smith') // Ordered by name
        ->where('users.1.name', 'John Doe')
    );
});
