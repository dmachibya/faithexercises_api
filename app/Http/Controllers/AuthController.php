<?php

namespace App\Http\Controllers;

use Google\Client as GoogleClient;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function google(Request $request)
    {
        $data = $request->validate(['idToken' => 'required|string']);

        $clientId = env('GOOGLE_CLIENT_ID_WEB');
        if (!$clientId) {
            throw ValidationException::withMessages([
                'idToken' => 'Server misconfiguration: GOOGLE_CLIENT_ID_WEB is not set.',
            ]);
        }

        $client = new GoogleClient(['client_id' => $clientId]);
        $payload = $client->verifyIdToken($data['idToken']);

        if (!$payload) {
            throw ValidationException::withMessages(['idToken' => 'Invalid token']);
        }

        $googleId = $payload['sub'] ?? null;
        if (!$googleId) {
            throw ValidationException::withMessages(['idToken' => 'Token missing subject']);
        }

        $email = $payload['email'] ?? null;
        $name = $payload['name'] ?? ($payload['given_name'] ?? 'User');
        $avatar = $payload['picture'] ?? null;

        $user = User::updateOrCreate(
            ['google_id' => $googleId],
            [
                'email' => $email,
                'name' => $name,
                'avatar' => $avatar,
            ]
        );

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['ok' => true]);
    }
}
