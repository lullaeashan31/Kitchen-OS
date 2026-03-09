<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\ProductionDay;
use App\Services\TaskService;
use App\Http\Requests\StoreTaskRequest;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TaskController extends Controller
{
    use AuthorizesRequests;

    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function store(StoreTaskRequest $request, string $kitchen_slug)
    {
        $this->authorize('create', Task::class);

        $productionDay = ProductionDay::findOrFail($request->production_day_id);

        $this->taskService->createTask(
            $productionDay,
            $request->title,
            $request->assigned_to
        );

        return back()->with('success', 'Task added.');
    }

    public function complete(Request $request, string $kitchen_slug, Task $task)
    {
        $this->authorize('complete', $task);

        $request->validate([
            'proof_image' => 'nullable|image|max:5120', // 5MB Max
        ]);

        $this->taskService->completeTask($task, $request->file('proof_image'));

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Task marked as completed.');
    }

    public function reopen(string $kitchen_slug, Task $task)
    {
        $this->authorize('update', $task); // Only managers can reopen? Or same as complete?

        $this->taskService->reopenTask($task);

        return back()->with('success', 'Task reopened.');
    }

    public function destroy(string $kitchen_slug, Task $task)
    {
        $this->authorize('delete', $task);
        $task->delete();
        return back()->with('success', 'Task deleted.');
    }
}
