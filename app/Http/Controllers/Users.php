<?php

namespace App\Http\Controllers;

use App\Models\administrators;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class Users extends Controller
{
    public function createUser(Request $request)
    {
        $valid = Validator::make($request->all(),[
            'username' => 'required|string|max:60|min:4',
            'password' => 'required|string|min:5|max:10',
        ]);

        if ($valid->fails()) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Request body is not valid',
                'violations' => $valid->errors(),
            ], 403);
        }

        if($request->get("role") !== "Admin")
        {
            return response()->json([
                'status' => 'forbidden',
                'message' => 'You are not the administrator',
            ],403);
        }

        if(User::where('username',$request->username)?->first())
        {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Username already exists',
            ],400);
        }
        
        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'created_at' => now('Asia/Jakarta'),
            'updated_at' => now('Asia/Jakarta'),
        ]);

        return response()->json([
            'status' => 'success',
            'username' => $user->username,
        ],200);
    }

    public function getUser(Request $request)
    {
        $User = User::all();
        
        if($request->get('user') === "Admin")
        {
            return response()->json([
                'totalElements' => $User->count(),
                'content' => $User,
            ]);
        }

        return response()->json([
            'status' => 'forbidden',
            'message' => 'You are not the administrator',
        ],403);
    }

    public function userUpdate(Request $request, Int $id)
    {
        $valid = Validator::make($request->all(),[
            'username' => 'required|string|max:60|unique:users|min:4',
            'password' => 'required|string|min:5|max:10',
        ]);

        if ($valid->fails()) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Request body is not valid',
                'violations' => $valid->errors(),
            ], 403);
        }

        if($request->get("role") !== "Admin")
        {
            return response()->json([
                'status' => 'forbidden',
                'message' => 'You are not the administrator',
            ],403);
        }

        $user = User::find($id);

        if(!$user)
        {
            return response()->json([
                'status' => 'invalid',
                'message' => 'User not found',
            ],404);
        }

        $user->username = $request->username;
        $user->password = Hash::make($request->password);
        $user->updated_at = now('Asia/Jakarta');
        $user->save();

        return response()->json([
            'status' => 'success',
            'username' => $user->username,
        ],201);
    }

    public function userDelete(Request $request, Int $id)
    {
        if($request->get("role") !== "Admin")
        {
            return response()->json([
                'status' => 'forbidden',
                'message' => 'You are not the administrator',
            ],403);
        }

        $user = User::find($id);

        if(!$user)
        {
            return response()->json([
                'status' => 'not-found',
                'message' => 'User not found',
            ],403);
        }

        $user->update([
            'deleted_at' => now('Asia/Jakarta'),
            'delete_reason' => 'Deleted'
        ]);

        return response(status:204);
    }
}
