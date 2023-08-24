<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $table = "events";
    protected $primaryKey = 'id_event';
    
    public function post() {
        return $this->hasMany(Post::class, 'fk_id_event');
    }

    public function participants() {
        return $this->hasMany(Participants::class, 'fk_id_event');
    }

    protected $fillable = [
        'name',
        'description',
        'text',
        'start_date',
        'end_date',
        'private'
    ];
}
