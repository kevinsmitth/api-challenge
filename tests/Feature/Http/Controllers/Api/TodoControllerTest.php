<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('can list todo items when authenticated', function () {
    Sanctum::actingAs(
        User::factory()->create(),
    );

    $todo = \App\Models\Todo::factory()->create([
        'user_id' => auth()->id(),
    ]);

    $this->get(route('todos.index'))
        ->assertOk()
        ->assertSee($todo->title)
        ->assertSee($todo->description)
        ->assertSee($todo->completed)
        ->assertSee($todo->favorite)
        ->assertSee($todo->color);
});

test('cannot list todo items when not authenticated', function () {
    $this->get(route('todos.index'))
        ->assertStatus(302, 'Unauthorized');
});

test('can create todo item when authenticated', function () {
    Sanctum::actingAs(
        User::factory()->create(),
    );

    $this->post(route('todos.store'), [
        'title' => 'Buy milk',
        'description' => 'Buy milk',
        'completed' => false,
        'favorite' => false,
        'color' => '#ff0000',
    ])
        ->assertCreated()
        ->assertJson([
            'title' => 'Buy milk',
            'description' => 'Buy milk',
            'completed' => false,
            'favorite' => false,
            'color' => '#ff0000',
        ]);
});

test('cannot create todo item when not authenticated', function () {
    $this->post(route('todos.store'), [
        'title' => 'Buy milk',
        'description' => 'Buy milk',
        'completed' => false,
        'favorite' => false,
        'color' => '#ff0000',
    ])
        ->assertStatus(302, 'Unauthorized');
});

test('can update todo item when authenticated', function () {
    Sanctum::actingAs(
        User::factory()->create(),
    );

    $todo = \App\Models\Todo::factory()->create([
        'user_id' => auth()->id(),
    ]);

    $this->put(route('todos.update', $todo), [
        'title' => 'Buy milk',
        'description' => 'Buy milk',
        'completed' => false,
        'favorite' => false,
        'color' => '#ff0000',
    ])
        ->assertOk()
        ->assertJson([
            'title' => 'Buy milk',
            'description' => 'Buy milk',
            'completed' => false,
            'favorite' => false,
            'color' => '#ff0000',
        ]);
});

test('cannot update todo item when not authenticated', function () {
    $todo = \App\Models\Todo::factory()->create([
        'user_id' => User::factory()->create()->id,
    ]);

    $this->put(route('todos.update', $todo), [
        'title' => 'Buy milk',
        'description' => 'Buy milk',
        'completed' => false,
        'favorite' => false,
        'color' => '#ff0000',
    ])
        ->assertStatus(302, 'Unauthorized');
});

test('can delete todo item when authenticated', function () {
    Sanctum::actingAs(
        User::factory()->create(),
    );

    $todo = \App\Models\Todo::factory()->create([
        'user_id' => auth()->id(),
    ]);

    $this->delete(route('todos.destroy', $todo))
        ->assertOk();
});

test('cannot delete todo item when not authenticated', function () {
    $todo = \App\Models\Todo::factory()->create([
        'user_id' => User::factory()->create()->id,
    ]);

    $this->delete(route('todos.destroy', $todo))
        ->assertStatus(302, 'Unauthorized');
});

test('cannot update a todo item that does not belong to the user', function () {
    Sanctum::actingAs(
        User::factory()->create(),
    );

    $todo = \App\Models\Todo::factory()->create([
        'user_id' => User::factory()->create()->id,
    ]);

    $this->put(route('todos.update', $todo), [
        'title' => 'Buy milk',
        'description' => 'Buy milk',
        'completed' => false,
        'favorite' => false,
        'color' => '#ff0000',
    ])
        ->assertNotFound();
});
