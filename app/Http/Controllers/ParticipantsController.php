<?php

namespace App\Http\Controllers;

use App\Models\Events;
use App\Models\Participants;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ParticipantsController extends Controller
{
    public function List(Request $request) {
        return Participants::all();
    }

    public function CreateParticipant(Request $request) {
        $validator = Validator::make($request->all(), [
            'fk_id_event' => 'required | exists:events,id',
            'fk_id_user' => 'required | exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        return $this->GetNewParticipant($request);
    }

    public function GetNewParticipant(request $request) {
        $fk_id_event = $request->input('fk_id_event');
        $fk_id_user = $request->input('fk_id_user');

        $existingParticipant = Participants::where('fk_id_event', $fk_id_event)
            ->where('fk_id_user', $fk_id_user)
            ->withTrashed()
            ->first();

        if ($existingParticipant) {
            return response()->json(['error' => 'Este usuario ya forma parte de este evento'], 400);
        }

        return $this->SaveParticipant($fk_id_event, $fk_id_user);
    }

    public function SaveParticipant($fk_id_event, $fk_id_user) {
        $newParticipant = new Participants();
        $newParticipant -> fk_id_event = $fk_id_event;
        $newParticipant -> fk_id_user = $fk_id_user;
        $newParticipant -> rol = 'follower';
        $newParticipant -> save();
        return $newParticipant;
    }

    public function UnParticipate($id_participates) {
        $participant = Participants::find($id_participates);
        if (!$participant) {
            return response()->json(['error' => 'No existe.'], 404);
        }

        $participant->delete();
        return response()->json(['message' => 'El participante ha sido eliminado exitosamente.']);
    }

}
