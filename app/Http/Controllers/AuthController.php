<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // User aanmaken
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Direct inloggen
        Auth::login($user);

        return response()->json($user, 201);
    }

    /**
     * Log in an existing user.
     */
    public function login(Request $request): JsonResponse
    {
        // 1) Validatie
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // 2) Inloggen proberen
        if (! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // 3) Regenerate session en return user
        $request->session()->regenerate();

        return response()->json(Auth::user());
    }


    /**
     * Log out the authenticated user.
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out']);
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->redirect();
    }

    // 2) Callback ophalen, user vinden of aanmaken, token genereren
    public function handleGoogleCallback(Request $request)
    {
        // 1. Haal Google-user op
        $googleUser = Socialite::driver('google')
            ->user();

        // 2. Vind of maak interne User
        $user = User::firstOrCreate([
            'email' => $googleUser->getEmail()
        ], [
            'name'     => $googleUser->getName(),
            'avatar'   => $googleUser->getAvatar(),
            'provider' => 'google',
            'provider_id' => $googleUser->getId(),
            // je fillable / casts in User model moeten 'provider' & 'provider_id' bevatten
        ]);

        // 3. Maak Sanctum-token
        $token = $user->createToken('chat-session')->plainTextToken;

        // 4. Redirect terug naar je frontend, met token in query
        $frontend = config('app.frontend_url');
        return redirect($frontend . '/login/callback?token=' . $token);
    }
}
