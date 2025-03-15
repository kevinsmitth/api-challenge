<?php

use App\Http\Controllers\Api\TodoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::apiResources([
    'todos' => TodoController::class,
]);

Route::get('todos/search', [TodoController::class, 'search'])->name('todos.search');

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

