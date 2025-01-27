<?php

namespace App\Http\Middleware;

use App\Events\UserLoginProcess;
use App\Models\administrators;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class AuthGuardHandle
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response{

        if(!$request->hasHeader('Authorization'))
        {
            return response()->json([
                'status' => 'Unauthenticated',
                'message' => 'Missing Token',
            ]);
        }else if(!PersonalAccessToken::findToken($request?->bearerToken()))
        {
            return response()->json([
                'status' => 'Unauthenticated',
                'message' => 'Invalid Token',
            ]);
        }
        $user = PersonalAccessToken::findToken($request?->bearerToken());

        if(!empty(User::find($user?->tokenable_id)?->deleted_at) && strstr($user?->tokenable_type,'User'))
        {
            return response()->json([
                'status' => 'blocked',
                'message' => 'User blocked',
                'reason' => 'You have been blocked by an administrator',
            ],403);
        }

        if(strstr($user?->tokenable_type,'User'))
        {
            $user = User::find($user?->tokenable_id);
            $request->merge(["user" => $user]);
            event(new UserLoginProcess($user));
        } else if(strstr($user?->tokenable_type,'administrators'))
        {
            $user = administrators::find($user?->tokenable_id);
            $request->merge(["user" => $user]);
            event(new UserLoginProcess($user));
        }

        return $next($request);
    }
}
