<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Ticket\EditTicketController;
use App\Http\Controllers\Ticket\ShowTicketController;
use App\Http\Controllers\Auth\ShowLoginFormController;
use App\Http\Controllers\Ticket\IndexTicketController;
use App\Http\Controllers\Ticket\StoreTicketController;
use App\Http\Controllers\Ticket\CreateTicketController;
use App\Http\Controllers\Ticket\UpdateTicketController;

// Auth
Route::get('/login', ShowLoginFormController::class)->name('login');
Route::post('/login', LoginController::class);
Route::post('/logout', LogoutController::class)->name('logout');

// Tickets (auth required)
Route::middleware('auth')->group(function () {
    Route::get('/', IndexTicketController::class)->name('tickets.index');
    Route::get('/tickets/create', CreateTicketController::class)->name('tickets.create');
    Route::post('/tickets', StoreTicketController::class)->name('tickets.store');
    Route::get('/tickets/{ticket}', ShowTicketController::class)->name('tickets.show');
    Route::get('/tickets/{ticket}/edit', EditTicketController::class)->name('tickets.edit');
    Route::put('/tickets/{ticket}', UpdateTicketController::class)->name('tickets.update');
});
