<?php

use App\Models\User;
use Livewire\Volt\Volt;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response
        ->assertOk()
        ->assertSeeVolt('pages.auth.login');
});

test('admins can authenticate using the login screen', function () {
    $user = User::factory()->admin()->create();

    $component = Volt::test('pages.auth.login')
        ->set('form.username', $user->username)
        ->set('form.password', 'password');

    $component->call('login');

    $component
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->admin()->create();

    $component = Volt::test('pages.auth.login')
        ->set('form.username', $user->username)
        ->set('form.password', 'wrong-password');

    $component->call('login');

    $component
        ->assertHasErrors()
        ->assertNoRedirect();

    $this->assertGuest();
});

test('non-admin users can not authenticate using the admin login screen', function () {
    $user = User::factory()->create(); // role defaults to 'user'

    $component = Volt::test('pages.auth.login')
        ->set('form.username', $user->username)
        ->set('form.password', 'password');

    $component->call('login');

    $component
        ->assertHasErrors()
        ->assertNoRedirect();

    $this->assertGuest();
});

test('navigation menu can be rendered', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response
        ->assertOk()
        ->assertSeeVolt('pages.admin.dashboard')
        ->assertSee($user->name);
});

test('non-admin users are redirected away from the dashboard', function () {
    $user = User::factory()->create(); // role defaults to 'user'

    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    $component = Volt::test('layout.navigation');

    $component->call('logout');

    $component
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
});
