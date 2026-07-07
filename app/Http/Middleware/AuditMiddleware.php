<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuditMiddleware
{
    /**
     * Journalise toutes les actions critiques (POST, PUT, PATCH, DELETE).
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $user = $request->user();
            $data = [
                'actor_id' => $user?->id,
                'actor_email' => $user?->email,
                'tenant_id' => $user?->cabinet_id,
                'action' => $request->method(),
                'path' => $request->path(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'payload' => json_encode($request->except(['password', 'password_confirmation', '_token'])),
                'status_code' => $response->getStatusCode(),
                'created_at' => now(),
            ];

            try {
                DB::table('audit_logs')->insert($data);
            } catch (\Exception $e) {
                Log::warning('Échec audit log : ' . $e->getMessage());
            }
        }

        return $response;
    }
}
