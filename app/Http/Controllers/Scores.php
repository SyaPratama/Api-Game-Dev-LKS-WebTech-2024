<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Game_Version;
use App\Models\Score;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Scores extends Controller
{
    public function addScore(Request $request, String $slug)
    {

        $valid = Validator::make($request->all(),[
            "score" => "required|numeric"
        ]);

        if($valid->fails())
        {
            return response()->json($valid->errors(),403);
        }

        $findSlug = Game::where('slug',$slug)->firstOrFail();

        if(!$findSlug) return response()->json(["status" => "Not Found!", "message" => "Slug Didn't Found In Databases"],status: 404);

        $gameVersion = Game_Version::where("game_id",$findSlug->id)->firstOrFail();

        if(!$gameVersion) return response()->json(["status" => "Failed","message" => "No Game Version Already!"],404);

        Score::create([
            "user_id" => $request->get('user')?->id,
            "score" => $request->score,
            "game_version_id" => $gameVersion->id,
        ]);

        return response()->json(["status" => "success"],status:201);
    }

    public function getScore(Request $request, String $slug)
    {
        $gameExists = Game::where("slug",$slug)->firstOrFail();

        if(!$gameExists) return response()->json(["status" => "Failed",'message' => "Game Not Exists"],404);

        $gameVersion = Game_Version::where('game_id',$gameExists->id)->firstOrFail();

        if(!$gameVersion) return response()->json(["status" => "Failed","message" => "Game Version Not Exists"],404);

        $score = Score::join("users","scores.user_id", '=', 'users.id')->where('game_version_id',$gameVersion->id)->select("username","score","scores.created_at as timestamp")->get();

        return response()->json([
            "scores" => $score, 
        ]);
    }
}
