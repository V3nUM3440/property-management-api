<?php

namespace App\Http\Controllers;

use App\Http\Resources\Core\AppAnonymousResourceCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): AppAnonymousResourceCollection
    {

        $query = User::query();

        if ($keyword = $request->get('keyword')) {
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%");
                $q->orWhere('email', 'like', "%$keyword%");
                $q->orWhereHas('roles', function ($query) use ($keyword) {
                    $query->where('name', 'like', "%$keyword%");
                });
            });
        }

        if ($role = $request->get('role')) {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        if ($request->per_page == 'all') {
            return UserResource::collection($query->select(['id', 'name'])->get());
        }
        
        $this->authorize('viewAny', User::class);
        $users = $query->with(['roles'])
        ->whereHas('roles', function ($query) {
            $query->where('name', '!=', 'super-admin');
        })
        ->withCount(['units'])
        ->orderBy($request->order_by ?? 'name', $request->order_dir ?? 'asc')
        ->paginate($request->per_page ?? 15);
        return UserResource::collection($users);
    }

    public function show(User $user): UserResource
    {
        $this->authorize('view', User::class);

        $user->load(['roles.permissions', 'units' => function ($query) {
            $query->withCount('partitions');
        }, 'units.building']);

        return new UserResource($user);
    }

    public function store(Request $request): UserResource
    {
        $this->authorize('create', User::class);

        $data = $request->validate([
            'role' => ['required', 'exists:roles,name'],
            'name' => ['required', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        /** @var User $user */
        $user = User::create($data);
        $user->assignRole($data['role']);

        return new UserResource($user->load('roles'));
    }

    public function update(Request $request, User $user): UserResource
    {
        $this->authorize('update', $user);

        $data = $request->validate([
            'role' => ['required', 'exists:roles,name'],
            'name' => ['required', 'max:255'],
        ]);

        /** @var User $user */
        $user->update([ 'name' => $data['name'] ]);
        $user->roles()->detach();
        $user->assignRole($data['role']);

        return new UserResource($user->load('roles'));
    }

    public function destroy(User $user): Response
    {
        $this->authorize('delete', User::class);

        if (count($user->units) > 0) {
            return response([
                'message' => 'User still manages units',
            ], 400);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->noContent();
    }

    public function resetPassword(User $user): Response
    {
        $this->authorize('update', $user);

        $user->tokens()->delete();
        $password = rand(100000, 999999);
        $user->update([
            'password' => Hash::make($password),
        ]);

        return response($password);
    }    
}
