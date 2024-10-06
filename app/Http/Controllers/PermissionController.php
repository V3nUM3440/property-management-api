<?php

namespace App\Http\Controllers;

use App\Http\Resources\Core\AppAnonymousResourceCollection;
use App\Http\Resources\PermissionResource;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(): AppAnonymousResourceCollection
    {
        return PermissionResource::collection(Permission::select(['id', 'name'])->get());
    }
}
