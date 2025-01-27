<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    protected $fillable = [
        'user_id',
        'game_version_id',
        'score',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id',"id")->withDefault(function(User $user, Score $score){
            return $score->user_id  = $user->username;
        });
    }
}
