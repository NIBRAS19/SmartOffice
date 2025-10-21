<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ApiResponse, AuthorizesRequests;

    /**
     * Display a listing of users
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $departmentId = $request->input('department_id');
        $roleSlug = $request->input('role');

        $query = User::with(['roles', 'department']);

        // Apply filters
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        if ($roleSlug) {
            $query->whereHas('roles', function ($q) use ($roleSlug) {
                $q->where('slug', $roleSlug);
            });
        }

        // If user is manager, only show users in their department
        $user = $request->user();
        if ($user->hasRole('manager') && !$user->hasRole('admin')) {
            $query->where('department_id', $user->department_id);
        }

        $users = $query->latest()->paginate($perPage);

        return $this->successResponse([
            'users' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    /**
     * Store a newly created user
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'department_id' => $request->department_id,
        ]);

        // Assign roles if provided
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        } else {
            $user->assignRole('staff'); // Default role
        }

        $user->load('roles', 'department');

        return $this->createdResponse(
            new UserResource($user),
            'User created successfully'
        );
    }

    /**
     * Display the specified user
     */
    public function show(Request $request, User $user): JsonResponse
    {
        $this->authorize('view', $user);

        $user->load('roles', 'department', 'assignedTasks');

        return $this->successResponse(new UserResource($user));
    }

    /**
     * Update the specified user
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->only(['name', 'email', 'department_id']);

        // Only allow password update if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Update roles if provided and user is admin
        if ($request->has('roles') && $request->user()->hasRole('admin')) {
            $user->syncRoles($request->roles);
        }

        $user->load('roles', 'department');

        return $this->successResponse(
            new UserResource($user),
            'User updated successfully'
        );
    }

    /**
     * Remove the specified user
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        // Prevent self-deletion
        if ($request->user()->id === $user->id) {
            return $this->errorResponse('You cannot delete your own account', 400);
        }

        $user->delete();

        return $this->successResponse(null, 'User deleted successfully');
    }

    /**
     * Assign roles to a user
     */
    public function assignRoles(Request $request, User $user): JsonResponse
    {
        $this->authorize('assignRoles', $user);

        $request->validate([
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,slug'],
        ]);

        $user->syncRoles($request->roles);
        $user->load('roles');

        return $this->successResponse(
            new UserResource($user),
            'Roles assigned successfully'
        );
    }

    /**
     * Get users by department
     */
    public function byDepartment(Request $request, int $departmentId): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $users = User::with(['roles'])
            ->where('department_id', $departmentId)
            ->get();

        return $this->successResponse(UserResource::collection($users));
    }
}