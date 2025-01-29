<?php

namespace App\Http\Controllers;

use App\Models\Game_Version;
use Illuminate\Support\Str;
use App\Models\Game;
use App\Models\Score;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\File;
use Laravel\Sanctum\PersonalAccessToken;

class Games extends Controller
{
    public function createGame(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'title' => 'required|min:3,|max:60',
            'description' => 'required|min:0|max:200',
        ]);

        if ($valid->fails()) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Request body is not valid',
                'violations' => $valid->errors(),
            ], 403);
        }

        $slug = Str::slug($request->title);
        if (Game::where('slug', $slug)?->first()) {
            return response()->json([
                'status' => 'invalid',
                'slug' => 'Game title already exists'
            ], 400);
        }

        $game = Game::create([
            'title' => $request->title,
            'description' => $request->description,
            'slug' => $slug,
            'created_by' => $request->get('user')->id
        ]);

        return response()->json([
            'status' => 'success',
            'slug' => $game->slug,
        ], 201);
    }

    public function uploadGame(Request $request, String $slug)
    {
        $valid = Validator::make($request->all(), [
            'zipfile' => 'required|mimes:zip',
            'token' => 'required|min:10',
        ]);


        if ($valid->fails()) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Request body is not valid',
                'violations' => $valid->errors(),
            ], 403);
        }

        $findToken = PersonalAccessToken::findToken($request->token);

        if (!$findToken) {
            return response()->json([
                'status' => 'Unaunthenticated',
                'message' => 'Token Is Invalid!'
            ], 401);
        }

        $game = Game::where('slug', $slug)->first();
        if ($game?->created_by !== $findToken?->tokenable_id) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'User is not author of the game',
            ], 400);
        }

        $file = $request?->file('zipfile');
        $filename = time() . '_' . $file->getClientOriginalName();
        $filepath = $file->storeAs('uploads', $filename, 'public');
        $firstVersion = 1;

        $findVersion = Game_Version::where("game_id", '=', $game->id)->get();
        if (count($findVersion) > 0) {
            $firstVersion = (int)($findVersion[count($findVersion) - 1]->version) + 1;
        }
        Game_Version::create([
            'game_id' => $game->id,
            'version' => $firstVersion,
            'storage_path' => $filepath,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil Upload File Game',
        ], 201);
    }

    public function serveGame(Request $request, String $slug, Int $version)
    {
        $game = Game::where('slug', $slug)?->first();
        $versionGame = Game_Version::where('version', $version)?->first();
        if ($game?->id === $versionGame?->game_id && empty($game?->deleted_at)) {
            $url = Storage::temporaryUrl($versionGame->storage_path, now()->addMinutes(10));
            return response()->json([
                'status' => 'success',
                'path' => $url,
            ], 200);
        }
        return response(status: 204);
    }

    public function updateGame(Request $request, String $slug)
    {
        $valid = Validator::make($request->all(), [
            'title' => 'required|min:5',
            'description' => 'required'
        ]);

        if ($valid->fails()) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Request body is not valid',
                'violations' => $valid->errors(),
            ], 403);
        }

        $Game = Game::where('slug', $slug);

        if ($Game->firstOrFail()?->created_by === $request->get('user')?->id) {

            $Game->update([
                'title' => $request->title,
                'description' => $request->description,
                'updated_at' => now('Asia/Jakarta'),
            ]);

            return response()->json([
                'status' => 'success',
            ], 200);
        }

        return response()->json([
            'status' => 'forbidden',
            'message' => 'You are not the game author',
        ], 403);
    }

    public function deleteGame(Request $request, string $slug)
    {
        $game = Game::where('slug', $slug);
        if ($game->firstOrFail()?->created_by !== $request->get('user')?->id) {
            return response()->json([
                'status' => 'forbidden',
                'message' => 'You are not the game author',
            ], 403);
        }

        $gameVersion = Game_Version::where('game_id', $game->first()?->id);

        $game?->update([
            'deleted_at' => now('Asia/Jakarta'),
        ]);

        $gameVersion?->update([
            'deleted_at' => now('Asia/Jakarta'),
        ]);
        return response(status: 204);
    }

    public function getGame(Request $request)
    {
        $game = Game::all();
        $pageStart = $request->query("page") ?? 0;
        $pageSize = $request->query("size") ?? 10;
        $sortBy = $request->query("sortBy") ?? "title";
        $sortDir = $request->query("sortDir") ?? "asc";
        $pageCount = ceil(count($game) / $pageSize);
        $gameVersion = Game_Version::all();
        $game = collect(Game::orderBy($sortBy, $sortDir)->paginate($pageSize, ["*"], 'page', $pageStart, $pageCount))->toArray()["data"];
        $scores = Score::all();

        $coll = collect($game)->map(
            function ($game) use ($gameVersion, $scores) {
                $versions = collect($gameVersion)->where("game_id", '=', $game["id"]);

                if($versions->isNotEmpty())
                {
                    $lastVersion = $versions->last();
                    $author = User::where("id",'=',$game["created_by"])->first();

                    $scoreCount = collect($scores)->where("game_version_id",'=',$lastVersion->id)->count();

                    return [
                        "slug" => $game["slug"],
                        "title" => $game["title"],
                        "description" => $game["description"],
                        "thumbnail" => null,
                        "uploadTimestamp" => $game["created_at"],
                        "author" => $author->username,
                        "scoreCount" => $scoreCount,
                    ];
                }
                return;
            }

        )->values();

        return response()->json([
            "page" => $pageStart,
            "size" => $pageSize,
            "totalElement" => count($game),
            "content" => $coll
        ], 200);
    }
}
