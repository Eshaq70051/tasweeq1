<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => 'user',
            'is_admin' => false,
            'status' => 'active',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = User::where('email', $request->email)->first();
        
        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Account is not active'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

            public function googleLogin(Request $request)
    {
        $token = $request->input('id_token') 
            ?? $request->input('access_token') 
            ?? $request->input('token');

        if (empty($token) || !is_string($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Token is required (id_token, access_token, or token)',
                'errors' => [
                    'token' => ['The token field is required.']
                ]
            ], 422);
        }

        try {
            $googleId = null;
            $email = null;
            $name = null;
            $avatar = null;

            $client = Http::timeout(10)->withoutVerifying();

            // Step 1: Verify as Google ID Token (JWT) via Google's public tokeninfo endpoint
            $idTokenResponse = $client->get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $token,
            ]);

            if ($idTokenResponse->successful()) {
                $payload = $idTokenResponse->json();
                $googleId = $payload['sub'] ?? null;
                $email = $payload['email'] ?? null;
                $name = $payload['name'] ?? ($payload['given_name'] ?? 'Google User');
                $avatar = $payload['picture'] ?? null;
            } else {
                // Step 2: Verify as OAuth2 Bearer Access Token via Google UserInfo API
                $userInfoResponse = $client
                    ->withToken($token)
                    ->get('https://www.googleapis.com/oauth2/v3/userinfo');

                if ($userInfoResponse->successful()) {
                    $payload = $userInfoResponse->json();
                    $googleId = $payload['sub'] ?? null;
                    $email = $payload['email'] ?? null;
                    $name = $payload['name'] ?? ($payload['given_name'] ?? 'Google User');
                    $avatar = $payload['picture'] ?? null;
                } else {
                    // Step 3: Check tokeninfo as access_token
                    $accessTokenResponse = $client->get('https://oauth2.googleapis.com/tokeninfo', [
                        'access_token' => $token,
                    ]);

                    if ($accessTokenResponse->successful()) {
                        $payload = $accessTokenResponse->json();
                        $googleId = $payload['sub'] ?? ($payload['user_id'] ?? null);
                        $email = $payload['email'] ?? null;
                        $name = $payload['name'] ?? 'Google User';
                    }
                }
            }

            // Step 4: Socialite fallback if still no email
            if (!$email && class_exists(Socialite::class)) {
                try {
                    $socialiteUser = Socialite::driver('google')->userFromToken($token);
                    $googleId = $socialiteUser->getId();
                    $email = $socialiteUser->getEmail();
                    $name = $socialiteUser->getName();
                    $avatar = $socialiteUser->getAvatar();
                } catch (\Throwable $socialiteEx) {
                    Log::warning('Socialite fallback failed: ' . $socialiteEx->getMessage());
                }
            }

            if (!$email) {
                return response()->json([
                    'success' => false,
                    'message' => 'تعذر التحقق من صحة رمز Google. يرجى التأكد من صلاحية الحساب والمحاولة مجدداً.',
                ], 401);
            }

            // Find or create user
            $user = User::where('email', $email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $name ?: 'Google User',
                    'email' => $email,
                    'google_id' => $googleId,
                    'is_google_account' => true,
                    'avatar' => $avatar,
                    'password' => Hash::make(Str::random(24)),
                    'role' => 'user',
                    'is_admin' => false,
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]);
            } else {
                if ($googleId && $user->google_id !== $googleId) {
                    $user->google_id = $googleId;
                }
                $user->is_google_account = true;
                if (!$user->avatar && $avatar) {
                    $user->avatar = $avatar;
                }
                if ($user->status !== 'active') {
                    return response()->json([
                        'success' => false,
                        'message' => 'الحساب غير مفعّل، يرجى التواصل مع الإدارة.'
                    ], 403);
                }
                $user->save();
            }

            $authToken = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $user,
                    'access_token' => $authToken,
                    'token_type' => 'Bearer'
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('Google login error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Google authentication error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only(['name', 'email', 'phone']));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Send reset link logic here
        return response()->json([
            'success' => true,
            'message' => 'Reset link sent to your email'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully'
        ]);
    }
} 
