<?php

namespace App\Http\Controllers;

use App\Http\Resources\Core\AppAnonymousResourceCollection;
use App\Http\Resources\RoleResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request): AppAnonymousResourceCollection
    {
        $query = Role::whereNot('name', 'super-admin');

        if ($keyword = $request->get('keyword')) {
            $query->where('name', 'like', "%$keyword%");
        }

        if ($request->per_page == 'all') {
            return RoleResource::collection($query->select(['id', 'name'])->get());
        }

        $this->authorize('viewAny', Role::class);
        $roles = $query->withCount(['permissions'])
        ->orderBy($request->order_by ?? 'name', $request->order_dir ?? 'asc')
        ->paginate($request->per_page ?? 15);
        return RoleResource::collection($roles);
    }

    public function show(Role $role): RoleResource
    {
        $this->authorize('view', $role);
        return new RoleResource($role->load(['permissions:id,name']));
    }

    public function store(Request $request): RoleResource
    {
        $this->authorize('create', Role::class);

        $data = $request->validate([
            'name' => ['required', 'string'],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web'])
        ->givePermissionTo(Permission::whereIn('name', $data['permissions'])->get());

        return new RoleResource($role->load(['permissions:id,name']));
    }

    public function update(Request $request, Role $role): RoleResource
    {
        $this->authorize('update', $role);

        $data = $request->validate([
            'name' => ['required', 'string'],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions(Permission::whereIn('name', $data['permissions'])->get());
        return new RoleResource($role->load(['permissions:id,name']));
    }

    public function destroy(Role $role): Response
    {
        $this->authorize('delete', $role);

        $users = User::whereHas('roles', function ($query) use ($role) {
            $query->where('name', $role->name);
        });
        if (count($users) > 0) {
            return response([
                'message' => 'Role is assiged to users',
            ], 400);
        }
        $role->delete();
        return response()->noContent();
    }
}
