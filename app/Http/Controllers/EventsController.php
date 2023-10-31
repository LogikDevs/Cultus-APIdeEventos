<?php

namespace App\Http\Controllers;

use App\Models\Events;
use App\Models\Participants;
use App\Models\EventInterests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class EventsController extends Controller
{
    public function List(Request $request) {
        return Events::all();
    }

    public function GetUserId(Request $request) {
        $tokenHeader = [ "Authorization" => $request -> header("Authorization")];
        $user = Http::withHeaders($tokenHeader)->get(getenv("API_AUTH_URL") . "/api/v1/validate");
        return $user['id'];
    }

    public function ListOne(Request $request, $id_event) {
        $tokenHeader = [ "Authorization" => $request -> header("Authorization")];
        return $this->GetEventDetails($id_event, $tokenHeader);
    }

    public function ListFollowed(Request $request) {
        $tokenHeader = ["Authorization" => $request->header("Authorization")];
        $id_user = $this->GetUserId($request);
        $events = $this->GetFollowedEventDetails($id_user, $tokenHeader);

        return $events;
    }

    public function ListInterested(Request $request) {
        $tokenHeader = ["Authorization" => $request->header("Authorization")];
        $id_user = $this->GetUserId($request); 
        $interests = $this->GetUserInterests($request, $id_user);
        $eventDetails = $this->GetInterestedEventDetails($interests, $id_user, $tokenHeader);

        return array_values($eventDetails);
    }

    public function GetUserInterests(Request $request, $id_user) {
        $route = getenv("API_AUTH_URL") . "/api/v1/likes/user/$id_user";

        $tokenHeader = [ "Authorization" => $request->header("Authorization")];
        $response = Http::withHeaders($tokenHeader)->get($route);

        if ($response->successful()) {
            return $response->json()['interests'];
        }
        return [];
    }

    private function GetEventDetails($eventId, $tokenHeader) {
        $event = $this->GetEvent($eventId);
        $event['admin'] = $this->GetAdmin($event[0]['id']);
        $event['interests'] = $this->GetInterestsFromEvent($event[0]['id'], $tokenHeader);
        $event['updates'] = $this->GetEventUpdates($event[0]['id'], $tokenHeader)->json();
        
            return $event;
    }

    public function GetEvent($eventId) {
        return Events::where('id', $eventId)->get();
    }

    private function GetAdmin($eventId) {
        $adminParticipant = Participants::where('fk_id_event', $eventId)
                                        ->where('rol', 'admin')
                                        ->first();
        if ($adminParticipant) {
            return [
                'name' => $adminParticipant->user->name,
                'surname' => $adminParticipant->user->surname,
            ];
        }
        return null;
    }

    public function GetInterestsFromEvent($fk_id_event, $tokenHeader) {
        $eventInterest = EventInterests::where('fk_id_event', $fk_id_event)->get();
        $int = [];
        return $this->GetInterestName($eventInterest, $int, $tokenHeader);
    }

    public function GetInterestName($eventInterest, $int, $tokenHeader) {
        foreach ($eventInterest as $a) {
            $fk_id_label = $a['fk_id_label'];
            $ruta = getenv("API_AUTH_URL") . "/api/v1/interest/$fk_id_label";
            $response = Http::withHeaders($tokenHeader)->get($ruta);

            if ($response->successful() && isset($response['interest'])) {
                $int[] = $response['interest'];
            }
        }

        return $int;
    }

    public function GetEventUpdates ($fk_id_event, $tokenHeader) {
        $ruta = getenv("API_POST_URL") . "/api/v1/posts/event/$fk_id_event";
        return $response = Http::withHeaders($tokenHeader)->get($ruta);
    }

    private function GetFollowedEventDetails($id_user, $tokenHeader) {
        $followedEvents = Participants::where('fk_id_user', $id_user)->get();
        $events = [];

        foreach ($followedEvents as $f) {
            $event = $this->GetEventDetails($f['fk_id_event'], $tokenHeader);
            $events[] = $event;
        }

        return $events;
    }

    private function GetInterestedEventDetails($interests, $id_user, $tokenHeader) {
        $eventDetails = [];

        foreach ($interests as $interest) {
            $eventInterests = $this->GetEventInterests($interest['id_label']);
            $events= $this->GetEventFromInterest($eventInterests, $eventDetails, $id_user, $tokenHeader);
        }

        return $events;
    }

    public function GetEventInterests($fk_id_label) {
        return EventInterests::where('fk_id_label', $fk_id_label)->get();
    }

    public function GetEventFromInterest($eventInterests, $eventDetails, $id_user, $tokenHeader) {
        foreach ($eventInterests as $eventInterest) {
            $event = $this->GetEventDetails($eventInterest['fk_id_event'], $tokenHeader);

            if ($event[0]['private'] && !$this->UserParticipatesEvent($id_user, $event[0]['id'])) {
                continue;
            }

            if ($this->UserParticipatesEvent($id_user, $event[0]['id'])) {
                continue;
            }

            $eventDetails[$event[0]['id']] = $event;
        }
        
        return $eventDetails;
    }

    public function UserParticipatesEvent($id_user, $event_id) {
        $participant = Participants::where('fk_id_user', $id_user)
                                  ->where('fk_id_event', $event_id)
                                  ->first();
        return !is_null($participant);
    }

    public function CreateEvent(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'nullable | max:200',
            'text' => 'required | max:600',
            'cover' => 'nullable | file | mimes:jpeg,png,mp4 | max:2048',
            'start_date' => 'required | date | after_or_equal:now',
            'end_date' => 'required | date | after:start_date',
            'private' => 'required | boolean'
        ]);

        return $this->ValidateNewEvent($request, $validator);
    }

    
    public function ValidateNewEvent(Request $request, $validator) {        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        
        $event = $this->SaveEvent($request);
        $admin = $this->SaveAdmin($request, $event);
        return $this->ReturnNewEvent($event, $admin);
    }

    public function ReturnNewEvent($event, $admin) {        
        $newCreatedEvent = $event;
        $newCreatedEvent['admin'] = $this->GetAdmin($event['id_event']);
        return $newCreatedEvent;
    }

    public function SaveEvent(request $request) {
        $newEvent = new Events();

        $cover = $this->ValidateCover($request);
        $newEvent -> name = $request->input('name');
        $newEvent -> description = $request->input('description');
        $newEvent -> text = $request->input('text');
        $newEvent -> cover = basename($cover);
        $newEvent -> start_date = $request->input('start_date');
        $newEvent -> end_date = $request->input('end_date');
        $newEvent -> private = $request->input('private');
        
        return $this->TransactionSaveEvent($newEvent);
    }

    public function ValidateCover(Request $request) {        
        if ($request->hasFile('cover')){
            $image = $request->file('cover');
            $imageExtension = $image->getClientOriginalExtension();
            $path = $image->store('/public/cover_event');
            return $path;
        }
    }
    
    public function TransactionSaveEvent($newEvent) {        
        try {
            DB::raw('LOCK TABLE events WRITE');
            DB::beginTransaction();
            $newEvent -> save();
            DB::commit();
            DB::raw('UNLOCK TABLES');
            return $newEvent;
        } catch (\Illuminate\Database\QueryException $th) {
            DB::rollback();
            return $th->getMessage();
        }
        catch (\PDOException $th) {
            return response("Permission to DB denied",403);
        }
    }

    public function SaveAdmin(request $request, $event) {
        $id_user = $this->GetUserId($request);
        $newAdmin = new Participants();
        $newAdmin -> fk_id_user = $id_user;
        $newAdmin -> fk_id_event = $event->id_event;
        $newAdmin -> rol = 'admin';
        $newAdmin -> save();

        return $this->TransactionSaveAdmin($newAdmin);
    }

    public function TransactionSaveAdmin($newAdmin) {        
        try {
            DB::raw('LOCK TABLE participants WRITE');
            DB::beginTransaction();
            $newAdmin -> save();
            DB::commit();
            DB::raw('UNLOCK TABLES');
            return $newAdmin;
        } catch (\Illuminate\Database\QueryException $th) {
            DB::rollback();
            return $th->getMessage();
        }
        catch (\PDOException $th) {
            return response("Permission to DB denied",403);
        }
    }
}
