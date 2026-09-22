<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/search', SearchController::class)->name('search');
    Route::get('/activity-logs', ActivityLogController::class)->name('activity-logs.index');
    Route::resource('tasks', TaskController::class);
    Route::patch('tasks/{task}/restore', [TaskController::class, 'restore'])->withTrashed()->name('tasks.restore');

    // Clients
    Route::resource('clients', ClientController::class);
    Route::patch('clients/{client}/restore', [ClientController::class, 'restore'])->withTrashed()->name('clients.restore');

    // Projects
    Route::resource('projects', ProjectController::class);
    Route::patch('projects/{project}/staff', [ProjectController::class, 'assignStaff'])->name('projects.staff');
    Route::patch('projects/{project}/restore', [ProjectController::class, 'restore'])->withTrashed()->name('projects.restore');

    // Service requests + workflow actions
    Route::resource('service-requests', ServiceRequestController::class);
    Route::patch('service-requests/{service_request}/assign', [ServiceRequestController::class, 'assign'])->name('service-requests.assign');
    Route::patch('service-requests/{service_request}/status', [ServiceRequestController::class, 'updateStatus'])->name('service-requests.status');
    Route::post('service-requests/{service_request}/comments', [ServiceRequestController::class, 'addComment'])->name('service-requests.comments');
    Route::patch('service-requests/{service_request}/restore', [ServiceRequestController::class, 'restore'])->withTrashed()->name('service-requests.restore');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
