<?php

namespace App\Observers;

use App\Models\System\SystemAuditLog;
use Illuminate\Support\Facades\Request;

class SystemAuditObserver
{
    protected function logAction($model, $action)
    {
        // On ne loggue pas les logs d'audit eux-mêmes pour éviter une boucle infinie
        if ($model instanceof SystemAuditLog) {
            return;
        }

        $userId = auth()->id() ?? null;

        SystemAuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id ?? null,
            'old_values' => $action !== 'created' ? $model->getOriginal() : null,
            'new_values' => $action !== 'deleted' ? $model->getAttributes() : null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent')
        ]);
    }

    public function created($model)
    {
        $this->logAction($model, 'created');
    }

    public function updated($model)
    {
        $this->logAction($model, 'updated');
    }

    public function deleted($model)
    {
        $this->logAction($model, 'deleted');
    }
}
