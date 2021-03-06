<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use VerifiesEmails;

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create($validatedData);

        // Raise user registered event
        event(new Registered($user));

        $accessToken = $user->createToken('authToken')->accessToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($user),
                'access_token' => $accessToken,
            ],
        ]);
    }

    public function login(Request $request)
    {
        $loginData = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($loginData)) {
            return response()->json([
                'success' => false, 
                'message' => 'Invalid credentials',
            ]);
        }

        /** @var User */
        $user = Auth::user();
        $accessToken = $user->createToken('authToken')->accessToken;

        return response()->json([
            'success' => true, 
            'data' => [
                'user' => new UserResource($user), 
                'access_token' => $accessToken,
            ],
        ]);
    }
}
