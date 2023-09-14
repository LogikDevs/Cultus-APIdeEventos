<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\EventInterestsController;
use App\Http\Controllers\ParticipantsController;

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

Route::prefix('v1')->middleware('auth:api')->group(function(){
    Route::get('/events', [EventsController::class, 'List']);
    Route::get('/event/interested', [EventsController::class, 'ListInterested']);
    Route::get('/event/followed', [EventsController::class, 'ListFollowed']);
    Route::post('/event/create', [EventsController::class, 'CreateEvent']);
    Route::post('/event/interests/create', [EventInterestsController::class, 'CreateEventInterests']);

    Route::get('/events/participants', [ParticipantsController::class, 'List']);
    Route::get('/event/participants/{id_event}', [ParticipantsController::class, 'ListParticipants']);
    Route::post('/events/participants/create', [ParticipantsController::class, 'CreateParticipant']);
    Route::post('/events/participants/delete/{id}', [ParticipantsController::class, 'UnParticipate']);
});
