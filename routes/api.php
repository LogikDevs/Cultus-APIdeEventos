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

Route::middleware(Autenticacion::class)->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->middleware(Autenticacion::class)->group(function(){
    Route::get('/events', [EventsController::class, 'List']);
    Route::get('/events/{d}', [EventsController::class, 'GetEventDetails']);
    Route::get('/event/admin/{id_event}', [EventsController::class, 'GetAdmin']);
    Route::get('/event/interested', [EventsController::class, 'ListInterested']);
    Route::get('/event/followed', [EventsController::class, 'ListFollowed']);
    Route::post('/event/create', [EventsController::class, 'CreateEvent']);

    Route::post('/event/interests/create', [EventInterestsController::class, 'CreateEventInterests']);

    Route::get('/participants', [ParticipantsController::class, 'List']);
    Route::get('/participants/{id_event}', [ParticipantsController::class, 'ListParticipants']);
    Route::post('/participant/create', [ParticipantsController::class, 'CreateParticipant']);
    Route::post('/participant/delete', [ParticipantsController::class, 'UnParticipate']);
    Route::post('/participates/{id_event}', [ParticipantsController::class, 'AsignRoleToParticipant']);
});