<?php

namespace App\Http\Controllers;

use App\Models\administrators;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class Authentication extends Controller
{
    public function SignUp(Request $request)
    {
        $valid = Validator::make($request->all(),[
            'username' => 'required|string|unique:administrators|max:60|min:4',
            'password' => 'required|string|min:5|max:10',
        ]);

        if ($valid->fails()) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Request body is not valid',
                'violations' => $valid->errors(),
            ], 403);
        }

        $user = administrators::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'created_at' => now('Asia/Jakarta'),
            'updated_at' => now('Asia/Jakarta'),
        ]);

        if($user)
        {
            return response()->json([
                'status' => 'success',
                'token' => $user->createToken('token',["*"],now()->addHour(24))?->plainTextToken,
            ],200);
        }
    }

    public function SignIn(Request $request)
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

        $user = administrators::where('username',$request->username)?->first();

        if($user && Hash::check($request->password,$user?->password))
        {
            return response()->json([
                'status' => 'success',
                'token' => $user->createToken('token',["*"],now()->addHour(24))?->plainTextToken,
            ],200);
        }

        $user = User::where('username',$request->username)?->first();

        if($user && Hash::check($request->password,$user?->password))
        {
            return response()->json([
                'status' => 'success',
                'token' => $user->createToken('token',["*"],now()->addHour(24))?->plainTextToken,
            ],200);
        }
        return response()->json([
            'status' => 'invalid',
            'message' => 'Wrong username or password',
        ],401);
    }

    public function SignOut(Request $request)
    {
        $request->user()?->currentAccessToken()->delete();
        return response()->json([
            'status' => 'success',
        ],200);
    }
}
