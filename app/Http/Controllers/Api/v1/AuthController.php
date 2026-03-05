<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\LoginRequest;
use App\Http\Resources\User\UserProfileResource;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        // Log::alert($request);
        $user = User::where('user_name', $request->user_name)->where('active', true)->orWhere('email', $request->email)->first();

        // Log::alert($user);
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(
                [
                    'ErrorCode' => '401',
                    'ErrorMessage' => __('general.loginFailed'),
                ],
                401
            );
        }

        $token = $user->createToken($request->user_name)->plainTextToken;
        return response()->json(
            [
                'id' => $user->id,
                'name' => $user->name,
                'token' => $token,
            ],
            200
        );
    }

    public function me()
    {
        return new UserResource(Auth::user());
    }

    public function profile()
    {
        return new UserProfileResource(Auth::user());
    }

    public function ho_me()
    {
        // return Auth::user();
        return new UserResource(Auth::user());
    }

    public function logout()
    {
        return Auth::user();
    }
}
