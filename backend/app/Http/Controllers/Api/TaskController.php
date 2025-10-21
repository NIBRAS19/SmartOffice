<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use ApiResponse, AuthorizesRequests;

    /**
     * Display a listing of tasks
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Task::class);

        $perPage = $request->input('per_page', 15);
        $status = $request->input('status');
        $departmentId = $request->input('department_id');
        $assignedTo = $request->input('assigned_to');

        $user = $request->user();
        $query = Task::with(['department', 'assignee', 'assigner']);

        // Role-based filtering
        if ($user->hasRole('admin')) {
            // Admin sees all tasks
        } elseif ($user->hasRole('manager')) {
            // Manager sees tasks in their department
            $query->where('department_id', $user->department_id);
        } else {
            // Staff sees only their assigned tasks
            $query->where('assigned_to', $user->id);
        }

        // Apply filters
        if ($status) {
            $query->where('status', $status);
        }

        if ($departmentId && ($user->hasRole('admin') || $user->hasRole('manager'))) {
            $query->where('department_id', $departmentId);
        }

        if ($assignedTo && ($user->hasRole('admin') || $user->hasRole('manager'))) {
            $query->where('assigned_to', $assignedTo);
        }

        $tasks = $query->latest()->paginate($perPage);

        return $this->successResponse([
            'tasks' => TaskResource::collection($tasks),
            'meta' => [
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
            ],
        ]);
    }

    /**
     * Store a newly created task
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status ?? Task::STATUS_PENDING,
            'department_id' => $request->department_id,
            'assigned_to' => $request->assigned_to,
            'assigned_by' => $request->user()->id,
            'due_date' => $request->due_date,
        ]);

        $task->load('department', 'assignee', 'assigner');

        return $this->createdResponse(
            new TaskResource($task),
            'Task created successfully'
        );
    }

    /**
     * Display the specified task
     */
    public function show(Request $request, Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $task->load('department', 'assignee', 'assigner');

        return $this->successResponse(new TaskResource($task));
    }

    /**
     * Update the specified task
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $user = $request->user();
        $data = [];

        // Staff can only update status
        if ($user->hasRole('staff') && !$user->hasAnyRole(['manager', 'admin'])) {
            if ($request->has('status')) {
                $data['status'] = $request->status;
            }
        } else {
            // Manager and Admin can update all fields
            $data = $request->only([
                'title',
                'description',
                'status',
                'department_id',
                'assigned_to',
                'due_date'
            ]);
        }

        $task->update($data);
        $task->load('department', 'assignee', 'assigner');

        return $this->successResponse(
            new TaskResource($task),
            'Task updated successfully'
        );
    }

    /**
     * Remove the specified task
     */
    public function destroy(Request $request, Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return $this->successResponse(null, 'Task deleted successfully');
    }

    /**
     * Mark task as completed
     */
    public function complete(Request $request, Task $task): JsonResponse
    {
        $this->authorize('complete', $task);

        if ($task->status === Task::STATUS_COMPLETED) {
            return $this->errorResponse('Task is already completed', 400);
        }

        $task->markAsCompleted();
        $task->load('department', 'assignee', 'assigner');

        return $this->successResponse(
            new TaskResource($task),
            'Task marked as completed'
        );
    }

    /**
     * Update task status
     */
    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $request->validate([
            'status' => ['required', 'in:' . implode(',', Task::statuses())],
        ]);

        $task->update(['status' => $request->status]);
        $task->load('department', 'assignee', 'assigner');

        return $this->successResponse(
            new TaskResource($task),
            'Task status updated successfully'
        );
    }

    /**
     * Reassign task to another user
     */
    public function reassign(Request $request, Task $task): JsonResponse
    {
        $this->authorize('reassign', $task);

        $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
        ]);

        $task->update([
            'assigned_to' => $request->assigned_to,
            'assigned_by' => $request->user()->id,
        ]);

        $task->load('department', 'assignee', 'assigner');

        return $this->successResponse(
            new TaskResource($task),
            'Task reassigned successfully'
        );
    }

    /**
     * Get my tasks (for authenticated user)
     */
    public function myTasks(Request $request): JsonResponse
    {
        $user = $request->user();
        $status = $request->input('status');

        $query = Task::with(['department', 'assignee', 'assigner'])
            ->where('assigned_to', $user->id);

        if ($status) {
            $query->where('status', $status);
        }

        $tasks = $query->latest()->get();

        return $this->successResponse(TaskResource::collection($tasks));
    }

    /**
     * Get task statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Task::query();

        // Filter based on role
        if ($user->hasRole('staff')) {
            $query->where('assigned_to', $user->id);
        } elseif ($user->hasRole('manager')) {
            $query->where('department_id', $user->department_id);
        }

        $stats = [
            'total' => $query->count(),
            'pending' => (clone $query)->where('status', Task::STATUS_PENDING)->count(),
            'in_progress' => (clone $query)->where('status', Task::STATUS_IN_PROGRESS)->count(),
            'completed' => (clone $query)->where('status', Task::STATUS_COMPLETED)->count(),
            'overdue' => (clone $query)
                ->where('status', '!=', Task::STATUS_COMPLETED)
                ->where('due_date', '<', now())
                ->count(),
        ];

        return $this->successResponse($stats);
    }
}