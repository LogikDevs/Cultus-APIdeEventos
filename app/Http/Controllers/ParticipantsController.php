<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Events;
use App\Models\Participants;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ParticipantsController extends Controller
{
    public function List(Request $request) {
        return Participants::all();
    }
    
    public function GetUser(Request $request) {
        $tokenHeader = [ "Authorization" => $request -> header("Authorization")];
        return Http::withHeaders($tokenHeader)->get(getenv("API_AUTH_URL") . "/api/v1/validate");
    }
    
    public function ListParticipants($id_event) {
        $participants = [];
        $par = Participants::where('fk_id_event', $id_event)->get();
        foreach ($par as $p) {
            $rol = $p['rol'];
            $fk_id_user = $p['fk_id_user'];
            $user = $this->GetUserData($fk_id_user);
            $user['rol'] = $rol;
            array_push($participants, $user);
        }

        return $participants;
    }

    private function GetUserData($fk_id_user) {
        $user = User::find($fk_id_user);
        return $user->only(['id', 'name', 'surname', 'profile_pic']);
    }

    public function CreateParticipant(Request $request) {
        $validator = Validator::make($request->all(), [
            'fk_id_event' => 'required | exists:events,id'
        ]);

        return $this->ValidateNewParticipant($request, $validator);
    }

    public function ValidateNewParticipant(Request $request, $validator) {        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $existingParticipant = $this->ValidateExistingParticipant($request);

        if ($existingParticipant) {
            return response()->json(['error' => 'Este usuario ya forma parte de este evento'], 400);
        }

        return $this->SaveParticipant($request);
    }

    public function ValidateExistingParticipant(request $request) {
        $user = $this->GetUser($request);
        return Participants::where('fk_id_event', $request['fk_id_event'])
            ->where('fk_id_user', $user['id'])
            ->withTrashed()
            ->first();
    }

    public function SaveParticipant(request $request) {
        $user = $this->GetUser($request);

        $newParticipant = new Participants();
        $newParticipant -> fk_id_event = $request->input('fk_id_event');
        $newParticipant -> fk_id_user = $user['id'];
        $newParticipant -> rol = 'follower';
        
        return $this->TransactionSaveParticipant($newParticipant);
    }
    
    public function TransactionSaveParticipant($newParticipant) {        
        try {
            DB::raw('LOCK TABLE participants WRITE');
            DB::beginTransaction();
            $newParticipant -> save();
            DB::commit();
            DB::raw('UNLOCK TABLES');
            return $newParticipant;
        } catch (\Illuminate\Database\QueryException $th) {
            DB::rollback();
            return $th->getMessage();
        }
        catch (\PDOException $th) {
            return response("Permission to DB denied",403);
        }
    }

    public function UnParticipate(Request $request) {
        $existingParticipant = $this->ValidateExistingParticipant($request);

        if ($existingParticipant) {
            return $this->TransactionUnParticipate($existingParticipant);
        }

        return response ("El usuario no forma parte de este evento, por ende, no puede dejar de seguirlo.", 400);
    }

    public function TransactionUnParticipate($participant) {
        try {
            DB::raw('LOCK TABLE participants WRITE');
            DB::beginTransaction();

            $participant->delete();

            DB::commit();
            DB::raw('UNLOCK TABLES');

            return response()->json(['message' => 'El participante ha sido eliminado exitosamente.']);
        } catch (\Illuminate\Database\QueryException $th) {
            DB::rollback();
            return response()->json(['error' => $th->getMessage()], 500);
        } catch (\PDOException $th) {
            return response("Permission to DB denied", 403);
        }
    }

}
