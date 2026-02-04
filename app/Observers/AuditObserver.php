<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        $this->logAction('created', $model, null, $model->getAttributes());
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        $this->logAction('updated', $model, $model->getOriginal(), $model->getChanges());
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->logAction('deleted', $model, $model->getAttributes(), null);
    }

    /**
     * Handle the Model "restored" event.
     */
    public function restored(Model $model): void
    {
        $this->logAction('restored', $model, null, $model->getAttributes());
    }

    /**
     * Log the action to audit_logs table
     */
    protected function logAction(string $action, Model $model, ?array $oldData, ?array $newData): void
    {
        // Skip logging for AuditLog model itself to avoid infinite loop
        if ($model instanceof AuditLog) {
            return;
        }

        // Skip if no authenticated user (e.g., seeding, migrations)
        if (!auth()->check()) {
            return;
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model' => get_class($model),
            'model_id' => $model->id,
            'old_data' => $oldData,
            'new_data' => $newData,
        ]);
    }
}
