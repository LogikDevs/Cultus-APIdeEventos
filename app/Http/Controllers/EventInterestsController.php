<?php

namespace App\Http\Controllers;

use App\Models\EventInterests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EventInterestsController extends Controller
{
    public function CreateEventInterests(Request $request) {
        $validation = $request->validate([
            'fk_id_label'=>'required | exists:interest_label,id_label',
            'fk_id_event'=>'required | exists:events,id'
        ]);

        $eventInterests = EventInterests::create($validation);
        return response()->json($eventInterests, 201);
    }
}
