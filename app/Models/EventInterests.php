<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventInterests extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $table = "event_interests";
    protected $primaryKey = 'id_event_interest';
    
    public function label() {
        return $this->belongsTo(interest::class, "fk_id_label");
    }

    public function event() {
        return $this->belongsTo(Events::class, "fk_id_event");
    }

    protected $fillable = [
        'fk_id_event',
        'fk_id_label'
    ];
}
