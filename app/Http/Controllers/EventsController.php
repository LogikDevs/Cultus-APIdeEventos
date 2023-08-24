<?php

namespace App\Http\Controllers;

use App\Models\Events;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventsController extends Controller
{

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
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'nullable | max:200',
            'text' => 'required | max:600',
            'cover' => 'required | file | mimes:jpeg,png,mp4 | max:2048',
            'start_date' => 'required | date | after_or_equal:now',
            'end_date' => 'required | date | after:start_date',
            'private' => 'required | boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        return $this->SaveEvent($request);
        //$this->SaveInterests($request);
    }

    public function SaveEvent(request $request) {
    //GET cover
        $file = $request->file('cover_file');
        $path = $file->store('public/cover_event');
        $mediaEvent->cover = $path;



        $newEvent = new Events();
        $newEvent -> name = $request->input('name');
        $newEvent -> description = $request->input('description');
        $newEvent -> text = $request->input('text');
        $newEvent -> cover = $mediaEvent;
        $newEvent -> start_date = $request->input('start_date');
        $newEvent -> end_date = $request->input('end_date');
        $newEvent -> private = $request->input('private');
        $newEvent -> save();
            
        return $newEvent;
    }








    public function SaveInterests(Request $request) {
        echo "oliwi";
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
