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
    public function GetUser(Request $request) {
        $tokenHeader = [ "Authorization" => $request -> header("Authorization")];
        return Http::withHeaders($tokenHeader)->get(getenv("API_AUTH_URL") . "/api/v1/validate");
    }

    public function GetEventDetails(Request $request, $eventToList) {
        $tokenHeader = [ "Authorization" => $request->header("Authorization")];

        $event['event'] = $eventToList;
        $event['admin'] = $this->GetAdmin($eventToList['id']);
        $event['interests'] = $this->GetInterestsFromEvent($eventToList['id'], $tokenHeader);
        $event['participants'] = $this->GetEventParticipants($eventToList['id']);
        $event['updates'] = $this->GetEventUpdates($eventToList['id'], $tokenHeader);

        return $event;
    }

    public function GetAdmin($eventId) {
        $adminParticipant = Participants::where('fk_id_event', $eventId)
                                        ->where('rol', 'admin')
                                        ->first();

        if ($adminParticipant) {
            return [
                'id' => $adminParticipant->user->id,
                'name' => $adminParticipant->user->name,
                'surname' => $adminParticipant->user->surname,
                'profile_pic' => $adminParticipant->user->profile_pic,
                'rol' => $adminParticipant->rol
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

    public function GetEventParticipants($eventId) {
        $participants = Participants::where('fk_id_event', $eventId)->get();
        $participantData = [];

        foreach ($participants as $participant) {
            $participantData[] = [
                'id' => $participant->user->id,
                'name' => $participant->user->name,
                'surname' => $participant->user->surname,
                'profile_pic' => $participant->user->profile_pic,
                'rol' => $participant->rol,
            ];
        }

        return $participantData;
    }

    public function GetEventUpdates ($fk_id_event, $tokenHeader) {
        $ruta = getenv("API_POST_URL") . "/api/v1/posts/event/$fk_id_event";
        $updates = $response = Http::withHeaders($tokenHeader)->get($ruta);

        if ($updates->successful()) {
            return $updates->json();
        }

        return null;
    }

    public function GetEvent($eventId) {
        return Events::where('id', $eventId)->first();
    }

    public function List(Request $request) {
        return Events::all();
    }

    public function ListOne(Request $request, $id_event) {
        $tokenHeader = [ "Authorization" => $request -> header("Authorization")];
        $event = $this->GetEvent($id_event);
        return response ($this->GetEventDetails($request, $event), 200);
    }

    public function ListFollowed(Request $request) {
        $user = $this->GetUser($request);
        $followedEvents = Participants::where('fk_id_user', $user['id'])->get();
        $events = $this->GetFollowedEvents($request, $followedEvents, $user['id']);

        return $events;
    }
    
    private function GetFollowedEvents(Request $request, $followedEvents, $id_user) {
        $events = [];
        $tokenHeader = ["Authorization" => $request->header("Authorization")];

        foreach ($followedEvents as $f) {
            $event = $this->GetEvent($f['fk_id_event']);
            $eventDetails = $this->GetEventDetails($request, $event);
            $events[] = $eventDetails;
        }

        return $this->EventEndData($events);
    }

    private function EventEndData($events) {
        usort($events, function ($a, $b) {
            return strtotime($b['event']['end_date']) - strtotime($a['event']['end_date']);
        });
    
        return $events;
    }

    public function ListInterested(Request $request) {
        $tokenHeader = ["Authorization" => $request->header("Authorization")];
        $user = $this->GetUser($request);
        $interests = $this->GetUserInterests($request, $user['id']);

        if ($interests) {
            $events = $this->GetInterestedData($request, $interests);
            $filteredEvents = $this->FilterPrivateEvents($events);
            $filteredEvents = array_unique($filteredEvents, SORT_REGULAR);
            $formattedEvents = array_values($filteredEvents);
    
            return response($formattedEvents, 200);
        }

        return response("No tienes intereses seleccionados", 204);
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

    public function GetInterestedData(Request $request, $interests) {
        $events = [];
        foreach ($interests as $i) {
            $eventInterests = $this->GetEventInterests($i['id_label']);
            $events = array_merge($events, $this->GetEventInformation($request, $eventInterests));
        }
        
        return $events;
    }

    public function GetEventInterests($fk_id_label) {
        return EventInterests::where('fk_id_label', $fk_id_label)->get();
    }

    public function GetEventInformation(Request $request, $eventInterests) {
        $events = [];
        foreach ($eventInterests as $e) {
            $event = $this->GetEvent($e['fk_id_event']);
            $eventDetails = $this->GetEventDetails($request, $event);
            $events[] = $eventDetails;
        }

        return $events;
    }

    public function FilterPrivateEvents($events) {
        return array_filter($events, function ($eventDetails) {
            return !$eventDetails['event']['private'];
        });
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
        $user = $this->GetUser($request);
        $newAdmin = new Participants();
        $newAdmin -> fk_id_user = $user['id'];
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
