<?php

namespace App\Http\Controllers;

use App\Models\EventInterests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class EventInterestsController extends Controller
{
    public function GetEvent($eventId) {
        return EventInterests::where('fk_id_event', $eventId)->get();
    }

    public function CreateEventInterests(Request $request) {
        $validator = Validator::make($request->all(), [
            'fk_id_label'=>'required | exists:interest_label,id_label',
            'fk_id_event'=>'required | exists:events,id'
        ]);

        return $this->ValidateEventInterests($request, $validator);
    }

    public function ValidateEventInterests(Request $request, $validator) {
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        return $this->SaveEventInterests($request);
    }

    public function SaveEventInterests(request $request) {
        $newEventInterests = new EventInterests();
        $newEventInterests -> fk_id_label = $request->input('fk_id_label');
        $newEventInterests -> fk_id_event = $request->input('fk_id_event');
        return $this->TransactionSaveEventInterests($newEventInterests);
    }

    public function TransactionSaveEventInterests($newEventInterests) {        
        try {
            DB::raw('LOCK TABLE event_interests WRITE');
            DB::beginTransaction();
            $newEventInterests -> save();
            DB::commit();
            DB::raw('UNLOCK TABLES');
            return $newEventInterests;
        } catch (\Illuminate\Database\QueryException $th) {
            DB::rollback();
            return $th->getMessage();
        }
        catch (\PDOException $th) {
            return response("Permission to DB denied",403);
        }
    }
}