<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game_Version extends Model
{
    protected $fillable = [
        "game_id",
        "version",
        "storage_path",
        "updated_at"
    ];
}
