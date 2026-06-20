<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\ApprovalStepLog;
use App\Models\ApprovalWorkflow;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service de gestion des workflows d'approbation.
 *
 * Gère le cycle de vie complet : création de demande,
 * traitement des approbations/rejets, escalade, notifications.
 */
class ApprovalService
{
    public function __construct(
        private SmsService $smsService,
        private WhatsAppService $whatsappService,
    ) {}

    /**
     * Créer une demande d'approbation pour un modèle (facture, devis, etc.)
     */
    public function createRequest(ApprovalWorkflow $workflow, string $modelType, int $modelId, User $requester): ApprovalRequest
    {
        $request = ApprovalRequest::create([
            'workflow_id'  => $workflow->id,
            'model_type'   => $modelType,
            'model_id'     => $modelId,
            'current_step' => 1,
            'status'       => 'pending',
            'requested_by' => $requester->id,
        ]);

        // Notifier le premier approbateur
        $this->notifyStepApprovers($request);

        Log::info('Approval request created', [
            'request_id' => $request->id,
            'workflow'   => $workflow->name,
            'model'      => "$modelType#$modelId",
        ]);

        return $request;
    }

    /**
     * Approuver une étape du workflow.
     */
    public function approve(ApprovalRequest $request, User $approver, ?string $comment = null): array
    {
        return $this->processStep($request, $approver, 'approved', $comment);
    }

    /**
     * Rejeter une demande (met fin au workflow).
     */
    public function reject(ApprovalRequest $request, User $approver, ?string $comment = null): array
    {
        return $this->processStep($request, $approver, 'rejected', $comment);
    }

    /**
     * Traiter une étape (approbation ou rejet).
     */
    private function processStep(ApprovalRequest $request, User $approver, string $action, ?string $comment): array
    {
        if ($request->status !== 'pending') {
            return ['success' => false, 'error' => "La demande est déjà {$request->status}."];
        }

        $workflow = $request->workflow;
        $steps = $workflow->steps ?? [];
        $currentStepIndex = $request->current_step - 1;

        if (!isset($steps[$currentStepIndex])) {
            return ['success' => false, 'error' => 'Étape de validation introuvable.'];
        }

        $step = $steps[$currentStepIndex];
        $approversList = $step['approvers'] ?? [];

        // Vérifier que l'utilisateur est bien un approbateur de cette étape
        if (!in_array($approver->id, $approversList) && !$approver->isSuperAdmin()) {
            return ['success' => false, 'error' => "Vous n'êtes pas habilité à valider cette étape."];
        }

        DB::beginTransaction();
        try {
            // Enregistrer le log
            ApprovalStepLog::create([
                'request_id'   => $request->id,
                'step_number'  => $request->current_step,
                'approver_id'  => $approver->id,
                'action'       => $action,
                'comment'      => $comment,
                'acted_at'     => now(),
            ]);

            if ($action === 'rejected') {
                $request->update([
                    'status'       => 'rejected',
                    'completed_at' => now(),
                ]);
                DB::commit();

                $this->notifyRequester($request, 'rejected', $comment);
                Log::info('Approval request rejected', ['request_id' => $request->id]);

                return ['success' => true, 'status' => 'rejected', 'final' => true];
            }

            // Vérifier s'il reste des approbateurs à cette même étape
            $stepLogs = ApprovalStepLog::where('request_id', $request->id)
                ->where('step_number', $request->current_step)
                ->where('action', 'approved')
                ->count();

            // Si approbation de type "any" (un seul suffit) ou "all" (tous requis)
            $approvalType = $step['type'] ?? 'any';
            $requiredApprovals = $approvalType === 'all' ? count($approversList) : 1;

            if ($stepLogs >= $requiredApprovals) {
                // Cette étape est complète → passer à la suivante
                $nextStep = $request->current_step + 1;

                if ($nextStep > count($steps)) {
                    // Toutes les étapes sont approuvées
                    $request->update([
                        'status'       => 'approved',
                        'completed_at' => now(),
                    ]);
                    DB::commit();

                    $this->notifyRequester($request, 'approved');
                    $this->triggerPostApproval($request);
                    Log::info('Approval request fully approved', ['request_id' => $request->id]);

                    return ['success' => true, 'status' => 'approved', 'final' => true];
                }

                // Passer à l'étape suivante
                $request->update(['current_step' => $nextStep]);
                DB::commit();

                $this->notifyStepApprovers($request);
                Log::info('Approval request advanced', ['request_id' => $request->id, 'step' => $nextStep]);

                return ['success' => true, 'status' => 'pending', 'step' => $nextStep];
            }

            DB::commit();

            // Encore besoin d'autres approbations à cette étape
            return ['success' => true, 'status' => 'pending', 'message' => "En attente d'autres approbations."];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Approval processing error', ['request_id' => $request->id, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Notifier les approbateurs de l'étape courante.
     */
    private function notifyStepApprovers(ApprovalRequest $request): void
    {
        $workflow = $request->workflow;
        $steps = $workflow->steps ?? [];
        $stepIndex = $request->current_step - 1;
        if (!isset($steps[$stepIndex])) return;

        $approverIds = $steps[$stepIndex]['approvers'] ?? [];
        $approvers = User::whereIn('id', $approverIds)->get();

        $model = $request->model_type ? $request->modelable : null;
        $label = $model ? "#{$request->model_id}" : "#{$request->id}";

        foreach ($approvers as $approver) {
            if ($approver->phone) {
                $this->smsService->send(
                    $approver->phone,
                    "GEL Cabinet — Nouvelle demande d'approbation (étape {$request->current_step}) : {$workflow->name} {$label}. Connectez-vous pour valider."
                );
            }
            if ($approver->whatsapp_number) {
                $this->whatsappService->sendText(
                    $approver->whatsapp_number,
                    "🟠 *Nouvelle demande d'approbation*\n\nWorkflow : {$workflow->name}\nRéf : {$label}\nÉtape : {$request->current_step}\n\nConnectez-vous pour valider."
                );
            }
        }
    }

    /**
     * Notifier le demandeur du résultat.
     */
    private function notifyRequester(ApprovalRequest $request, string $status, ?string $comment = null): void
    {
        $requester = $request->requester;
        if (!$requester) return;

        $workflow = $request->workflow;
        $model = $request->model_type ? $request->modelable : null;
        $label = $model ? "#{$request->model_id}" : "#{$request->id}";

        $message = $status === 'approved'
            ? "✅ Votre demande d'approbation {$workflow->name} {$label} a été *approuvée*."
            : "❌ Votre demande d'approbation {$workflow->name} {$label} a été *rejetée*."
                . ($comment ? "\nMotif : $comment" : '');

        if ($requester->phone) {
            $this->smsService->send($requester->phone, strip_tags($message));
        }
        if ($requester->whatsapp_number) {
            $this->whatsappService->sendText($requester->whatsapp_number, $message);
        }
    }

    /**
     * Action post-approbation (ex: marquer la facture comme validée).
     */
    private function triggerPostApproval(ApprovalRequest $request): void
    {
        try {
            if ($request->model_type === 'App\Models\ErpInvoice' && $model = $request->modelable) {
                $model->update(['validation_status' => 'approved']);
            }
        } catch (\Exception $e) {
            Log::warning('Post-approval trigger failed', ['request_id' => $request->id, 'error' => $e->getMessage()]);
        }
    }
}
