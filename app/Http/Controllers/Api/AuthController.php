<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'       => 'required|string',
            'email'      => 'required|email|unique:users',
            'password'   => 'required|min:6',
            'role'       => 'required|in:teacher,student',
            'student_id' => 'required_if:role,student|string|unique:users',
        ]);

        $user = User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'role'       => $request->role,
            'student_id' => $request->student_id,
        ]);

        // Auto-create Student profile for student registrations
        if ($request->role === 'student') {
            \App\Models\Student::create([
                'name'            => $request->name,
                'user_id'         => $user->id,
                'school_class_id' => null,
                'grade'           => '1',
                'reading_level'   => 1,
            ]);
        }

        $token = auth('api')->login($user);
        return $this->respondWithToken($token, $user);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return $this->respondWithToken($token, auth('api')->user());
    }

    public function me()
    {
        return response()->json(auth('api')->user());
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    private function respondWithToken($token, $user = null)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60,
            'user'         => $user,
        ]);
    }
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'avatar'     => 'nullable|string',
            'student_id' => 'nullable|string|unique:users,student_id,' . auth('api')->id(), // NEW: Validation
        ]);

        $user = auth('api')->user();
        $user->update([
            'name'       => $request->name,
            'avatar'     => $request->avatar ?? $user->avatar,
            'student_id' => $request->student_id ?? $user->student_id, // NEW: Updating the ID
        ]);

        return response()->json($user->fresh());
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:6|confirmed',
        ]);

        $user = auth('api')->user();

        if (!\Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 422);
        }

        $user->update(['password' => \Hash::make($request->password)]);

        return response()->json(['message' => 'Password updated successfully']);
    }

    public function deleteAccount()
    {
        $user = auth('api')->user();
        auth('api')->logout();
        $user->delete();

        return response()->json(['message' => 'Account deleted']);
    }
}