<?php

namespace App\Services;

use App\Models\Task;
use App\Models\ProductionDay;
use App\Enums\TaskStatus;

class TaskService
{
    protected $driveService;

    public function __construct(\App\Services\DriveService $driveService)
    {
        $this->driveService = $driveService;
    }

    public function createTask(ProductionDay $productionDay, string $title, ?int $assignedTo = null): Task
    {
        $task = $productionDay->tasks()->create([
            'title' => $title,
            'assigned_to' => $assignedTo,
            'status' => TaskStatus::Pending,
        ]);

        \App\Models\AuditLog::log('Created Task', $productionDay, null, ['task_title' => $title, 'assigned_to' => $assignedTo]);

        return $task;
    }

    public function completeTask(Task $task, $proofImage = null): bool
    {
        $proofPath = null;
        if ($proofImage) {
            // Folder: tasks/{year}/{month}
            $folderName = 'tasks/' . date('Y/m');
            $proofPath = $this->driveService->uploadFile($proofImage, $folderName);
        }

        $task->status = TaskStatus::Completed;
        if ($proofPath) {
            $task->proof_image_path = $proofPath;
        }
        $saved = $task->save();

        if ($saved) {
            \App\Models\AuditLog::log('Completed Task', $task->productionDay, ['status' => TaskStatus::Pending], ['status' => TaskStatus::Completed, 'task_title' => $task->title, 'proof' => $proofPath]);
        }

        return $saved;
    }

    public function reopenTask(Task $task): bool
    {
        $task->status = TaskStatus::Pending;
        $saved = $task->save();

        if ($saved) {
            \App\Models\AuditLog::log('Reopened Task', $task->productionDay, ['status' => TaskStatus::Completed], ['status' => TaskStatus::Pending, 'task_title' => $task->title]);
        }

        return $saved;
    }
}
