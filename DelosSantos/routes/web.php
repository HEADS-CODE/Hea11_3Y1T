<?php

use App\Http\Controllers\TaskController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LevelController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/members');

Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');

Route::resource('members', MemberController::class);
Route::post('/members/{member}/toggle-status', [MemberController::class, 'toggleStatus'])->name('members.toggle-status');
Route::resource('membership-categories', CategoryController::class)->except('show')->names('categories');
Route::resource('membership-levels', LevelController::class)->except('show')->names('levels');
