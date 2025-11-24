<?php

namespace App\Http\Controllers;

use Google\Client as GoogleClient;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

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

        $user = User::firstOrNew(['google_id' => $googleId]);
        $user->email = $email;
        $user->name = $name;
        $user->avatar = $avatar;
        if (!$user->exists) {
            // ensure non-nullable password column is satisfied; cast will hash
            $user->password = Str::random(40);
        }
        $user->save();

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

    public function register(Request $request)
    {
        // Debug logging
        \Log::info('Register endpoint hit', [
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'input' => $request->all()
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', PasswordRule::defaults()],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

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

    public function forgotPassword(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $status = Password::sendResetLink(['email' => $data['email']]);
        return response()->json(['status' => __($status)]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset(
            $data,
            function (User $user, string $password) {
                $user->password = $password;
                $user->save();
            }
        );

        return response()->json([
            'status' => __($status),
            'ok' => $status === Password::PASSWORD_RESET,
        ]);
    }

    public function me(Request $request)
    {
        $u = $request->user();
        return response()->json([
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'avatar' => $u->avatar,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'avatar' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'password' => ['sometimes', 'confirmed', PasswordRule::defaults()],
        ]);

        if (array_key_exists('password', $data) && $data['password'] === null) {
            unset($data['password']);
        }

        $user->fill($data);
        $user->save();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
            ],
        ]);
    }
}
