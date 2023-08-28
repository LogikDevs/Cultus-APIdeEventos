<?php

namespace App\Http\Controllers;

use App\Models\Events;
use App\Models\Participants;
use App\Models\EventInterests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class EventsController extends Controller
{
    public function List(Request $request) {
        return Events::all();
    }

    public function ListInterested($id_user) {
        $events = [];
        $eventU = [];
        $interests = $this->GetUserInterests($id_user);

        foreach ($interests as $i) {
            $eventInterests = $this->GetEventInterests($i['id_label']);
            foreach ($eventInterests as $e) {
                $event = $this->GetEvent($e['fk_id_event']);

                if ($event[0]['private'] && !$this->UserParticipatesEvent($id_user, $event[0]['id'])) {
                    continue;
                }
                if ($this->UserParticipatesEvent($id_user, $event[0]['id'])) {
                    continue;
                }
                
                $eventU[$event[0]['id']] = $event[0];
                $admin = $this->GetAdmin($event[0]['id']);
                $interests = $this->GetInterestsFromEvent($event[0]['id']);
                $eventU[$event[0]['id']]['admin'] = $admin;
                $eventU[$event[0]['id']]['interests'] = $interests;
            }
        }
        $events = array_values($eventU);
        return $events;
    }

    public function GetUserInterests($id_user) {
        $route = 'http://localhost:8000/api/v1/likes/user/' . $id_user . '/';
        $response = Http::get($route);

        if ($response->successful()) {
            return $response->json()['interests'];
        }
        return [];
    }

    public function GetEventInterests($fk_id_label) {
        return EventInterests::where('fk_id_label', $fk_id_label)->get();
    }

    public function GetEvent($fk_id_event) {
        return Events::where('id', $fk_id_event)->get();
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

    public function GetInterestsFromEvent($fk_id_event) {
        $eventInterest = EventInterests::where('fk_id_event', $fk_id_event)->get();
        $int = [];
        return $this->GetInterestName($eventInterest, $int);
    }

    public function GetInterestName($eventInterest, $int) {
        foreach ($eventInterest as $a) {
            $fk_id_label = $a['fk_id_label'];
            $ruta = 'http://localhost:8000/api/v1/interest/' . $fk_id_label;
            $response = Http::get($ruta);
            $int[] = $response['interest'];
        }

        return $int;
    }

    public function UserParticipatesEvent($id_user, $event_id) {
        $participant = Participants::where('fk_id_user', $id_user)
                                  ->where('fk_id_event', $event_id)
                                  ->first();
        return !is_null($participant);
    }











    public function ListFollowed($id_user) {
        $events = [];
        $followedEvents = Participants::where('fk_id_user', $id_user)->get();
        foreach ($followedEvents as $f) {
            $event = $this->GetEvent($f['fk_id_event']);
            foreach ($event as $e) {
                $admin = $this->GetAdmin($e['id']);
                $interests = $this->GetInterestsFromEvent($e['id']);
                $event['admin'] = $admin;
                $event['interests'] = $interests; 
            }

            $events[] = $event;
        }
        return $events;
    }


































/*
FUNCIONALLLLLLLLLLLLLLLLLLLL
    public function Create(request $request){
        $validation = self::CreateValidation($request);
        if ($validation->fails())
        return $validation->errors();
    
        return $this -> CreateRequest($request);
    }

    public function CreateEvents(request $request){
        $validation = Validator::make($request->all(),[
            'name' => 'required',
            'description' => 'nullable | max:200',
            'text' => 'required | max:600',
            'start_date' => 'required | date | after_or_equal:now',
            'end_date' => 'required | date | after:start_date',
            'private' => 'required | boolean'
        ]);
        return $validation;
    }

    public function CreateRequest(request $request)
    {
        $newEvent = new Events();
        $newEvent -> name = $request->input('name');
        $newEvent -> description = $request->input('description');
        $newEvent -> text = $request->input('text');
        $newEvent -> start_date = $request->input('start_date');
        $newEvent -> end_date = $request->input('end_date');
        $newEvent -> private = $request->input('private');
        $newEvent -> save();
            
        return $newEvent;
    }
*/

    public function CreateEvent(Request $request) {
        //date_default_timezone_set('America/Montevideo');
        $eventAll = [];
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'nullable | max:200',
            'text' => 'required | max:600',
            'cover' => 'nullable | file | mimes:jpeg,png,mp4 | max:2048',
            'start_date' => 'required | date | after_or_equal:now',
            'end_date' => 'required | date | after:start_date',
            'private' => 'required | boolean',
            'id_user' => 'required | exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

    
        $event = $this->SaveEvent($request);
        $admin = $this->SaveAdmin($request, $event);
        //$interests = $this->SaveInterests($request, $event);
        
        $newCreatedEvent = $event;
        $newCreatedEvent['admin'] = $this->GetAdmin($event['id_event']);
        //$newCreatedEvent['interests'] = $this->GetInterestsFromEvent($event['id_event']);
        return $newCreatedEvent;
    }

    public function SaveEvent(request $request) {
    //GET cover
        $file = $request->file('cover');
        $path = $file->store('public/cover_event');

        $newEvent = new Events();
        $newEvent -> name = $request->input('name');
        $newEvent -> description = $request->input('description');
        $newEvent -> text = $request->input('text');
        $newEvent -> cover = $path;
        $newEvent -> start_date = $request->input('start_date');
        $newEvent -> end_date = $request->input('end_date');
        $newEvent -> private = $request->input('private');
        $newEvent -> save();
            
        return $newEvent;
    }

    public function SaveAdmin(request $request, $event) {
        $newAdmin = new Participants();
        $newAdmin -> fk_id_user = $request->input('id_user');
        $newAdmin -> fk_id_event = $event->id_event;
        $newAdmin -> rol = 'admin';
        $newAdmin -> save();

        return $newAdmin;
    }







    public function SaveInterests(Request $request, $event) {
    /*
        $validation = $request->validate([
            'fk_id_label'=>'required | exists:interest_label,id_label',
            'fk_id_post'=>'required | exists:post,id_post'
        ]);

        $characterize = Characterizes::create($validation);
        return response()->json($characterize, 201);
    */
    }


}
