<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class EventsController extends Controller
{
    /**
     * Vérifie si les permissions de l'utilisateur ont changé depuis le dernier check.
     * Le frontend appelle cette endpoint toutes les 10 secondes (polling léger).
     */
    public function checkUpdates(): JsonResponse
    {
        $user = Auth::user();
        $key = 'perm_changed_' . $user->id;
        $changed = cache()->pull($key);

        if ($changed) {
            return response()->json([
                'updated' => true,
                'permissions' => $user->getFormattedPermissions(),
                'modules' => $user->getAccessibleModules(),
            ]);
        }

        return response()->json(['updated' => false]);
    }

    /**
     * Notifie qu'un utilisateur spécifique doit recharger ses permissions.
     * Appelé par UserController après modification des permissions.
     */
    public static function notifyUserPermissionsChanged(int $userId): void
    {
        cache()->put('perm_changed_' . $userId, now()->timestamp, 60);
    }
}
