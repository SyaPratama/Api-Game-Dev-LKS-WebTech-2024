<?php

namespace App\Http\Controllers;

use App\Models\administrators;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class Admins extends Controller
{
    public function createAdmin(Request $request)
    {
        $valid = Validator::make($request->all(),[
            "username" => "required|min:5|unique:administrators,username",
            "password" => "required|min:5",
        ]);

        if($valid->fails())
        {
            return response()->json($valid->errors(),403);
        }

        $admin = administrators::create([
            "username" => $request->username,
            "password" => Hash::make($request->password)
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'username' => $admin->username,
                'token' => $admin->createToken($admin->id)->plainTextToken,
            ]
        ],200);
    }

    public function getAdmin(Request $request)
    {
        $admin = administrators::all();
        
        if($request->get('role') == "Admin")
        {
            return response()->json([
                'totalElements' => $admin->count(),
                'content' => $admin,
            ]);
        }

        return response()->json([
            'status' => 'forbidden',
            'message' => 'You are not the administrator',
        ],403);
    }
}
