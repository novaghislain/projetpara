<?php

namespace App\Http\Controllers\GelConsultant;

use App\Http\Controllers\Controller;
use App\Models\Gel\ConsultantMission;
use App\Models\Gel\ConsultantDeliverable;
use App\Models\Gel\ConsultantAudit;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DeliverableController extends Controller
{
    public function store(Request $request, ConsultantMission $mission)
    {
        $user = auth()->user();

        // SÉCURITÉ SERVEUR : vérifier que ce consultant est assigné à cette mission
        if ($mission->consultant_id !== $user->id) {
            abort(403, 'Vous n\'avez pas accès à ce dossier.');
        }

        // SÉCURITÉ SERVEUR : vérifier que la mission n'est pas expirée
        if ($mission->isExpired()) {
            abort(403, 'Votre accès à ce dossier est expiré. Dépôt de livrable impossible.');
        }

        $request->validate([
            'file'  => 'required|file|max:51200', // 50 MB max
            'notes' => 'nullable|string|max:2000',
        ]);

        $file = $request->file('file');
        $path = $file->store('consultant_deliverables/' . $mission->id, 'local');

        $deliverable = ConsultantDeliverable::create([
            'mission_id'    => $mission->id,
            'consultant_id' => $user->id,
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(),
            'notes'         => $request->notes,
        ]);

        // Mettre à jour le statut de la mission
        $mission->update(['status' => 'livre']);

        // Journaliser le dépôt
        ConsultantAudit::create([
            'mission_id' => $mission->id,
            'user_id'    => $user->id,
            'action'     => 'Dépôt de livrable : ' . $file->getClientOriginalName(),
            'ip_address' => request()->ip(),
            'details'    => 'Notes : ' . ($request->notes ?? 'Aucune'),
        ]);

        // Notifier l'Administrateur de l'entreprise
        Notification::create([
            'user_id'    => $mission->created_by,
            'type'       => 'consultant_livrable',
            'message'    => 'Le consultant ' . $user->name . ' a déposé un livrable sur le dossier : ' . $mission->title,
            'source_url' => route('gel-admin.consultants.show', $mission->id),
            'is_read'    => false,
        ]);

        return back()->with('success', 'Votre livrable a été déposé avec succès et l\'administrateur a été notifié.');
    }

    public function destroy(ConsultantDeliverable $deliverable)
    {
        $user = auth()->user();

        // Un consultant peut supprimer ses propres livrables (pas les docs originaux de l'entreprise)
        if ($deliverable->consultant_id !== $user->id) {
            abort(403);
        }

        Storage::disk('local')->delete($deliverable->file_path);
        $deliverable->delete();

        return back()->with('success', 'Livrable supprimé.');
    }
}
