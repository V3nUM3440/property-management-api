<?php

namespace App\Http\Controllers;

use App\Http\Resources\Core\AppAnonymousResourceCollection;
use App\Http\Resources\TenantResource;
use App\Models\Partition;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index(Request $request): AppAnonymousResourceCollection
    {
        $query = Tenant::query();

        if ($keyword = $request->get('keyword')) {
            $query->where(function (Builder $q) use ($keyword) {
                $q->whereRaw("first_name || ' ' || last_name LIKE '%$keyword%' ");
                $q->orWhere('email', 'like', "%$keyword%");
                $q->orWhere('phone', 'like', "%$keyword%");
                $q->orWhere('identity_number', 'like', "%$keyword%");
                $q->orWhere('passport_number', 'like', "%$keyword%");
                $q->orWhereHas('contracts', function ($query) use ($keyword) {
                    $query->where('reference', 'like', "%$keyword%");
                });
            });
        }

        if ($request->per_page == 'all') {
            return TenantResource::collection($query->get());
        }

        $this->authorize('viewAny', Tenant::class);
        $tenants = $query->with(['last_contract', 'parent.last_contract'])
        ->orderBy($request->order_by ?? 'first_name', $request->order_dir ?? 'asc')
        ->paginate($request->per_page ?? 15);

        return TenantResource::collection($tenants);
    }

    public function show(Tenant $tenant): TenantResource
    {
        $this->authorize('view', $tenant);

        $tenant->load([
            'contracts' => function ($query) {
                $query->withTrashed();
                $query->with(['contractable', 'tenant', 'payments']);
            },
            'parent' => function ($query) {
                $query->with([
                    'contracts' => function ($q) {
                        $q->withTrashed();
                        $q->with(['contractable', 'tenant', 'payments']);
                }, 'security_deposit.depositable', 'security_deposit.tenant']);
            }, 'security_deposit.depositable', 'security_deposit.tenant'
        ]);
        foreach ($tenant->contracts as $contract) {
            if ($contract->contractable_type == Partition::class) {
                $contract->contractable->load(['unit', 'building']);
            } else {
              $contract->contractable->load(['building']);
            }
        }

        if (isset($tenant->parent)) {
            foreach ($tenant->parent->contracts as $contract) {
                if ($contract->contractable_type == Partition::class) {
                    $contract->contractable->load(['unit', 'building']);
                } else {
                  $contract->contractable->load(['building']);
                }
            }
        }

        if ($tenant->security_deposit) {
            if ($tenant->security_deposit->depositable_type == Partition::class) {
                $tenant->security_deposit->depositable->load(['unit', 'building']);
            } else {
                $tenant->security_deposit->depositable->load(['building']);
            }
        }
        
        if (isset($tenant->parent)) {
            if ($tenant->parent->security_deposit) {
                if ($tenant->parent->security_deposit->depositable_type == Partition::class) {
                    $tenant->parent->security_deposit->depositable->load(['unit', 'building']);
                } else {
                    $tenant->parent->security_deposit->depositable->load(['building']);
                }
            }
        }

        return new TenantResource($tenant);
    }

    public function store(Request $request): TenantResource
    {
        $this->authorize('create', Tenant::class);

        $data = $request->validate([
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'gender' => ['required', 'string'],
            'dob' => ['required', 'date'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
            'identity_number' => ['nullable', 'string'],
            'identity_expiry' => ['nullable', 'date'],
            'passport_number' => ['nullable', 'string'],
            'passport_expiry' => ['nullable', 'date'],
        ]);

        $tenant = Tenant::create($data);
        return new TenantResource($tenant);
    }

    public function update(Request $request, Tenant $tenant): TenantResource
    {
        $this->authorize('update', $tenant);

        $data = $request->validate([
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'gender' => ['required', 'string'],
            'dob' => ['required', 'date'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
            'identity_number' => ['nullable', 'string'],
            'identity_expiry' => ['nullable', 'date'],
            'passport_number' => ['nullable', 'string'],
            'passport_expiry' => ['nullable', 'date'],
        ]);

        $tenant->update($data);
        return new TenantResource($tenant);
    }
}
