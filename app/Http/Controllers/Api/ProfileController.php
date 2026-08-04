<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/**
 * Contrôleur API du profil utilisateur.
 *
 * Gère la mise à jour du mot de passe, la gestion
 * de la photo de profil et les informations personnelles.
 */
class ProfileController extends Controller
{
    /**
     * Met à jour le mot de passe de l'utilisateur connecté.
     *
     * @param  Request  $request
     * @return JsonResponse
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
     * Met à jour la photo de profil de l'utilisateur connecté.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function updatePhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
        ]);

        $user = $request->user();

        // Supprimer l'ancienne photo si elle existe
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
     * Supprime la photo de profil de l'utilisateur connecté.
     *
     * @param  Request  $request
     * @return JsonResponse
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
     * Retourne les données du profil de l'utilisateur connecté avec photo.
     *
     * @param  Request  $request
     * @return JsonResponse
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
