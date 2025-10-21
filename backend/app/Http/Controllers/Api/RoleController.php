<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Http\Resources\PermissionResource;
use App\Models\Role;
use App\Models\Permission;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of roles
     */
    public function index(Request $request): JsonResponse
    {
        if (!$request->user()->hasRole('admin')) {
            return $this->forbiddenResponse();
        }

        $roles = Role::with('permissions')
            ->withCount('users')
            ->get();

        return $this->successResponse(RoleResource::collection($roles));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request): JsonResponse
    {
        if (!$request->user()->hasRole('admin')) {
            return $this->forbiddenResponse();
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'unique:roles,slug'],
            'description' => ['nullable', 'string'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $request->name,
            'slug' => $request->slug ?? Str::slug($request->name),
            'description' => $request->description,
        ]);

        // Attach permissions if provided
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        $role->load('permissions');

        return $this->createdResponse(
            new RoleResource($role),
            'Role created successfully'
        );
    }

    /**
     * Display the specified role
     */
    public function show(Request $request, Role $role): JsonResponse
    {
        if (!$request->user()->hasRole('admin')) {
            return $this->forbiddenResponse();
        }

        $role->load('permissions')
            ->loadCount('users');

        return $this->successResponse(new RoleResource($role));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        if (!$request->user()->hasRole('admin')) {
            return $this->forbiddenResponse();
        }

        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'unique:roles,slug,' . $role->id],
            'description' => ['sometimes', 'nullable', 'string'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $data = $request->only(['name', 'description']);
        
        if ($request->has('slug')) {
            $data['slug'] = $request->slug;
        }

        $role->update($data);

        // Update permissions if provided
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        $role->load('permissions');

        return $this->successResponse(
            new RoleResource($role),
            'Role updated successfully'
        );
    }

    /**
     * Remove the specified role
     */
    public function destroy(Request $request, Role $role): JsonResponse
    {
        if (!$request->user()->hasRole('admin')) {
            return $this->forbiddenResponse();
        }

        // Prevent deletion of default roles
        if (in_array($role->slug, ['admin', 'manager', 'staff'])) {
            return $this->errorResponse('Cannot delete default system roles', 400);
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return $this->errorResponse(
                'Cannot delete role with assigned users. Please reassign users first.',
                400
            );
        }

        $role->delete();

        return $this->successResponse(null, 'Role deleted successfully');
    }

    /**
     * Get all permissions
     */
    public function permissions(Request $request): JsonResponse
    {
        if (!$request->user()->hasRole('admin')) {
            return $this->forbiddenResponse();
        }

        $permissions = Permission::all();

        return $this->successResponse(PermissionResource::collection($permissions));
    }

    /**
     * Attach permissions to a role
     */
    public function attachPermissions(Request $request, Role $role): JsonResponse
    {
        if (!$request->user()->hasRole('admin')) {
            return $this->forbiddenResponse();
        }

        $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->permissions()->syncWithoutDetaching($request->permissions);
        $role->load('permissions');

        return $this->successResponse(
            new RoleResource($role),
            'Permissions attached successfully'
        );
    }

    /**
     * Detach permissions from a role
     */
    public function detachPermissions(Request $request, Role $role): JsonResponse
    {
        if (!$request->user()->hasRole('admin')) {
            return $this->forbiddenResponse();
        }

        $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->permissions()->detach($request->permissions);
        $role->load('permissions');

        return $this->successResponse(
            new RoleResource($role),
            'Permissions detached successfully'
        );
    }
}