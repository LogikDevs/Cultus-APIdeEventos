<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participants extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $table = "participants";
    protected $primaryKey = 'id_participant';
    
    public function user() {
        return $this->belongsTo(user::class, "fk_id_user");
    }

    public function event() {
        return $this->belongsTo(Events::class, "fk_id_event");
    }

    protected $fillable = [
        'fk_id_event',
        'fk_id_label'
    ];
    use HasFactory;
}
