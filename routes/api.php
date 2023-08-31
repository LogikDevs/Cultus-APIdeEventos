<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\EventInterestsController;
use App\Http\Controllers\ParticipantsController;
use App\Http\Middleware\Autenticacion;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->middleware(Autenticacion::class)->group(function(){
    Route::get('/events', [EventsController::class, 'List']);
    Route::get('/events/interested/{id}', [EventsController::class, 'ListInterested']);
    Route::get('/events/followed/{id}', [EventsController::class, 'ListFollowed']);
    Route::post('/events/create', [EventsController::class, 'CreateEvent']);
    Route::post('/events/interests/create', [EventInterestsController::class, 'CreateEventInterests']);

    Route::get('/events/participants', [ParticipantsController::class, 'List']);
    Route::post('/events/participants/create', [ParticipantsController::class, 'CreateParticipant']);
    Route::post('/events/participants/delete/{id}', [ParticipantsController::class, 'UnParticipate']);
});
