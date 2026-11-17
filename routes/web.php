<?php

declare(strict_types=1);

use App\Http\Controllers\ColumnController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectBoardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkspaceMemberController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return to_route('dashboard');
    }

    return Inertia::render('Welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('verified')
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/workspaces', [WorkspaceController::class, 'store'])->name('workspaces.store');
    Route::get('/workspaces/{workspace}', [WorkspaceController::class, 'show'])
        ->name('workspaces.show');
    Route::get('/workspaces/{workspace}/members', [WorkspaceController::class, 'members'])
        ->name('workspaces.members');
    Route::post('/workspaces/{workspace}/members', [WorkspaceMemberController::class, 'store'])
        ->name('workspaces.members.store');
    Route::patch('/workspaces/{workspace}/members/{user}', [WorkspaceMemberController::class, 'update'])
        ->scopeBindings()
        ->name('workspaces.members.update');
    Route::delete('/workspaces/{workspace}/members/{user}', [WorkspaceMemberController::class, 'destroy'])
        ->scopeBindings()
        ->name('workspaces.members.destroy');

    Route::post('/workspaces/{workspace}/projects', [ProjectController::class, 'store'])
        ->name('projects.store');
    Route::get('/projects/{project}/board', [ProjectBoardController::class, 'show'])
        ->name('projects.board');

    Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::patch('/tasks/{task}/move', [TaskController::class, 'move'])->name('tasks.move');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/tasks/{task}/comments', [TaskCommentController::class, 'store'])
        ->name('tasks.comments.store');

    Route::post('/projects/{project}/columns', [ColumnController::class, 'store'])
        ->name('columns.store');
    Route::patch('/columns/{column}/move', [ColumnController::class, 'move'])->name('columns.move');
    Route::delete('/columns/{column}', [ColumnController::class, 'destroy'])->name('columns.destroy');
});

require __DIR__.'/auth.php';
