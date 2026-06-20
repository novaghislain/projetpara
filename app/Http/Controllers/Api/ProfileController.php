<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'Mot de passe mis à jour avec succès.',
        ]);
    }

    /**
     * Update the authenticated user's profile photo/avatar.
     */
    public function updatePhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
        ]);

        $user = $request->user();

        // Delete old photo if exists
        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        // Store new photo
        $path = $request->file('photo')->store('avatars', 'public');
        $user->photo = $path;
        $user->save();

        return response()->json([
            'message' => 'Photo de profil mise à jour avec succès.',
            'photo_url' => asset('storage/' . $path),
        ]);
    }

    /**
     * Delete the user's profile photo.
     */
    public function deletePhoto(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->photo = null;
        $user->save();

        return response()->json([
            'message' => 'Photo de profil supprimée.',
        ]);
    }

    /**
     * Get current user profile data with photo URL.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $photoUrl = null;

        if ($user->photo) {
            $photoUrl = asset('storage/' . $user->photo);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'photo' => $user->photo,
                'photo_url' => $photoUrl,
            ],
        ]);
    }
}
