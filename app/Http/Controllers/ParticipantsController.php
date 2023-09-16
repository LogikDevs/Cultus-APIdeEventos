<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Events;
use App\Models\Participants;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ParticipantsController extends Controller
{
    public function List(Request $request) {
        return Participants::all();
    }
    
    public function GetUserId(Request $request) {
        $tokenHeader = [ "Authorization" => $request -> header("Authorization")];
        $response = Http::withHeaders($tokenHeader)->get(getenv("API_AUTH_URL") . "/api/v1/validate");
        return $response['id'];
    }
    
    public function ListParticipants($id_event) {
        $participants = [];
        $par = Participants::where('fk_id_event', $id_event)->get();
        foreach ($par as $p) {
            $rol = $p['rol'];
            $fk_id_user = $p['fk_id_user'];
            $user = $this->GetUser($fk_id_user);
            $user['rol'] = $rol;
            array_push($participants, $user);
        }

        return $participants;
    }

    private function GetUser($fk_id_user) {
        $user = User::find($fk_id_user);
        return $user->only(['name', 'surname', 'profile_pic']);
    }

    public function CreateParticipant(Request $request) {
        $id_user = $this->GetUserId($request);

        $validator = Validator::make($request->all(), [
            'fk_id_event' => 'required | exists:events,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        return $this->GetNewParticipant($request, $id_user);
    }

    public function GetNewParticipant(request $request, $id_user) {
        $fk_id_event = $request->input('fk_id_event');

        $existingParticipant = Participants::where('fk_id_event', $fk_id_event)
            ->where('fk_id_user', $id_user)
            ->withTrashed()
            ->first();

        if ($existingParticipant) {
            return response()->json(['error' => 'Este usuario ya forma parte de este evento'], 400);
        }

        return $this->SaveParticipant($fk_id_event, $id_user);
    }

    public function SaveParticipant($fk_id_event, $id_user) {
        $newParticipant = new Participants();
        $newParticipant -> fk_id_event = $fk_id_event;
        $newParticipant -> fk_id_user = $id_user;
        $newParticipant -> rol = 'follower';
        $newParticipant -> save();
        return $newParticipant;
    }

    public function UnParticipate(Request $request) {
        $id_user = $this->GetUserId($request);

        $participant = Participants::find($id_user);
        if (!$participant) {
            return response()->json(['error' => 'El usuario no forma parte de este evento.'], 404);
        }

        $participant->delete();
        return response()->json(['message' => 'El participante ha sido eliminado exitosamente.']);
    }

}
