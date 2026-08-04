<?php

namespace App\Http\Controllers\GelSuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlatformSetting;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;

class PlatformConfigController extends Controller
{
    public function index()
    {
        $settings = PlatformSetting::all()->keyBy('key');
        
        return view('gel-super-admin.platform.config', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.value' => 'required',
            'settings.*.type' => 'required|in:boolean,integer,string,json',
            'settings.*.description' => 'nullable|string',
        ]);

        foreach ($validated['settings'] as $key => $data) {
            $value = $data['type'] === 'boolean' ? ($data['value'] === '1' || $data['value'] === 'true' || $data['value'] === 'on' || $data['value'] === true) : $data['value'];
            
            PlatformSetting::setValue($key, $value, $data['type'], $data['description'] ?? null);
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'event' => 'UPDATE_PLATFORM_CONFIG',
            'description' => "Mise à jour des paramètres globaux de la plateforme.",
            'ip_address' => request()->ip(),
            'auditable_type' => PlatformSetting::class,
            'auditable_id' => 0
        ]);

        return back()->with('success', 'Les paramètres de la plateforme ont été mis à jour.');
    }
}
