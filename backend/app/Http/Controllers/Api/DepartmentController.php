<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    use ApiResponse, AuthorizesRequests;

    /**
     * Display a listing of departments
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Department::class);

        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');

        $query = Department::with(['manager'])
            ->withCount(['users', 'tasks']);

        // Apply search filter
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        // If user is manager (not admin), only show their department
        $user = $request->user();
        if ($user->hasRole('manager') && !$user->hasRole('admin')) {
            $query->where('id', $user->department_id);
        }

        // Get all departments without pagination for simplicity
        $departments = $query->latest()->get();

        return $this->successResponse([
            'departments' => DepartmentResource::collection($departments),
        ]);
    }

    /**
     * Store a newly created department
     */
    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $department = Department::create([
            'name' => $request->name,
            'description' => $request->description,
            'manager_id' => $request->manager_id,
        ]);

        // If manager is assigned, update their role if needed
        if ($request->manager_id) {
            $manager = \App\Models\User::find($request->manager_id);
            if ($manager && !$manager->hasRole('manager')) {
                $manager->assignRole('manager');
            }
        }

        $department->load('manager', 'users');

        return $this->createdResponse(
            new DepartmentResource($department),
            'Department created successfully'
        );
    }

    /**
     * Display the specified department
     */
    public function show(Request $request, Department $department): JsonResponse
    {
        $this->authorize('view', $department);

        $department->load(['manager', 'users.roles', 'tasks'])
            ->loadCount(['users', 'tasks']);

        return $this->successResponse(new DepartmentResource($department));
    }

    /**
     * Update the specified department
     */
    public function update(UpdateDepartmentRequest $request, Department $department): JsonResponse
    {
        $data = $request->only(['name', 'description']);

        // Handle manager assignment (admin only)
        if ($request->has('manager_id') && $request->user()->hasRole('admin')) {
            $data['manager_id'] = $request->manager_id;

            // Update user's role if new manager assigned
            if ($request->manager_id) {
                $newManager = \App\Models\User::find($request->manager_id);
                if ($newManager && !$newManager->hasRole('manager')) {
                    $newManager->assignRole('manager');
                }
            }
        }

        $department->update($data);
        $department->load('manager', 'users');

        return $this->successResponse(
            new DepartmentResource($department),
            'Department updated successfully'
        );
    }

    /**
     * Remove the specified department
     */
    public function destroy(Request $request, Department $department): JsonResponse
    {
        $this->authorize('delete', $department);

        // Check if department has users
        if ($department->users()->count() > 0) {
            return $this->errorResponse(
                'Cannot delete department with existing users. Please reassign users first.',
                400
            );
        }

        $department->delete();

        return $this->successResponse(null, 'Department deleted successfully');
    }

    /**
     * Assign a manager to a department
     */
    public function assignManager(Request $request, Department $department): JsonResponse
    {
        $this->authorize('assignManager', $department);

        $request->validate([
            'manager_id' => ['required', 'exists:users,id'],
        ]);

        $manager = \App\Models\User::findOrFail($request->manager_id);

        // Ensure user has manager role
        if (!$manager->hasRole('manager')) {
            $manager->assignRole('manager');
        }

        // Update manager's department
        $manager->update(['department_id' => $department->id]);

        // Update department's manager
        $department->update(['manager_id' => $request->manager_id]);

        $department->load('manager', 'users');

        return $this->successResponse(
            new DepartmentResource($department),
            'Manager assigned successfully'
        );
    }

    /**
     * Get department statistics
     */
    public function statistics(Request $request, Department $department): JsonResponse
    {
        $this->authorize('view', $department);

        $stats = [
            'total_users' => $department->users()->count(),
            'total_tasks' => $department->tasks()->count(),
            'pending_tasks' => $department->tasks()->where('status', 'pending')->count(),
            'in_progress_tasks' => $department->tasks()->where('status', 'in_progress')->count(),
            'completed_tasks' => $department->tasks()->where('status', 'completed')->count(),
            'overdue_tasks' => $department->tasks()
                ->where('status', '!=', 'completed')
                ->where('due_date', '<', now())
                ->count(),
        ];

        return $this->successResponse($stats);
    }
}