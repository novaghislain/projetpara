<?php

namespace App\Http\Controllers\GelAdmin\Traits;

use Illuminate\Support\Facades\Auth;

trait HasAdminEntity
{
    /**
     * Retourne l'entité administrateur courante (Cabinet, Entreprise, ou Client).
     */
    protected function getAdminEntity()
    {
        $user = Auth::user();
        
        if ($user->cabinet) {
            return $user->cabinet;
        }
        
        if ($user->entreprise) {
            return $user->entreprise;
        }
        
        if ($user->client_id) {
            return $user->client;
        }
        
        return null;
    }
    
    protected function getAdminEntityType()
    {
        $user = Auth::user();
        
        if ($user->cabinet) return 'cabinet';
        if ($user->entreprise) return 'entreprise';
        if ($user->client_id) return 'client';
        
        return 'unknown';
    }
}
