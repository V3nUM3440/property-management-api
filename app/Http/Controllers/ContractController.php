<?php

namespace App\Http\Controllers;

use App\Http\Resources\ContractResource;
use App\Http\Resources\Core\AppAnonymousResourceCollection;
use App\Models\Contract;
use App\Models\Partition;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ContractController extends Controller
{
    public function index(Request $request): AppAnonymousResourceCollection
    {
        $this->authorize('viewAny', Contract::class);

        $query = Contract::query();

        if ($keyword = $request->get('keyword')) {
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('reference', 'like', "%$keyword%");
                $q->orWhereHas('tenant', function ($query) use ($keyword) {
                    $query->whereRaw("first_name || ' ' || last_name LIKE '%$keyword%' ");
                });
                $q->orWhereHasMorph('contractable', Unit::class, function ($query) use ($keyword) {
                    $query->where('name', 'like', "%$keyword%");
                    $query->orWhereHas('building', function ($query) use ($keyword) {
                        $query->where('name', 'like', "%$keyword%");
                    });
                });
                $q->orWhereHasMorph('contractable', Partition::class, function ($query) use ($keyword) {
                    $query->where('name', 'like', "%$keyword%");
                    $query->orWhereHas('unit', function ($query) use ($keyword) {
                        $query->where('name', 'like', "%$keyword%");
                        $query->orWhereHas('building', function ($query) use ($keyword) {
                            $query->where('name', 'like', "%$keyword%");
                        });
                    });
                });
            });
        }

        if ($buildingId = $request->get('building_id')) {
            $query->where(function (Builder $q) use ($buildingId) {
                $q->whereHasMorph('contractable', Unit::class, function ($query) use ($buildingId) {
                    $query->where('building_id', $buildingId);
                });
                $q->orWhereHasMorph('contractable', Partition::class, function ($query) use ($buildingId) {
                    $query->whereHas('unit', function ($query) use ($buildingId) {
                        $query->where('building_id', $buildingId);
                    });
                });
            });
        }

        if ($unitId = $request->get('unit_id')) {
            $query->where(function (Builder $q) use ($unitId) {
                $q->whereHasMorph('contractable', Unit::class, function ($query) use ($unitId) {
                    $query->where('id', $unitId);
                });
                $q->orWhereHasMorph('contractable', Partition::class, function ($query) use ($unitId) {
                    $query->where('unit_id', $unitId);
                });
            });
        }

        if ($partitionId = $request->get('partition_id')) {
            $query->whereHasMorph('contractable', Partition::class, function ($query) use ($partitionId) {
                $query->where('id', $partitionId);
            });
        }

        if ($type = $request->get('type')) {
            $query->where('contractable_type', $type == 'Unit' ? Unit::class : Partition::class);
        }

        if ($startDate = $request->get('start_date')) {
            $query->where('start_date', '>=', Carbon::parse($startDate)->subDay());
        }

        if ($endDate = $request->get('end_date')) {
            $query->where('end_date', '<=', Carbon::parse($endDate));
        }

        $tab = $request->get('tab');

        if ($tab == 'active') {
            $query->where('start_date', '<=', Carbon::today());
            $query->where('end_date', '>=', Carbon::today());
        } else if ($tab == 'expired') {
            $query->where('end_date', '<', Carbon::today());
        } else if ($tab == 'deleted') {
            $query->onlyTrashed();
        } else if ($tab == 'all') {
            $query->withTrashed();
        }

        $contracts = $query->with(['contractable', 'tenant.subtenants', 'tenant.security_deposit', 'payments'])
        ->orderBy($request->order_by ?? 'start_date', $request->order_dir ?? 'desc')
        ->paginate($request->per_page ?? 15);

        $contracts->loadMorph('contractable', [
            Unit::class => ['building'],
            Partition::class => ['unit', 'building'],
        ]);

        return ContractResource::collection($contracts);
    }

    public function show(string $id): ContractResource
    {
        $contract = Contract::withTrashed()->findOrFail($id);
        $this->authorize('view', $contract);

        $contract->load(['contractable', 'payments', 'tenant.subtenants']);
        if ($contract->contractable_type == Partition::class) {
            $contract->contractable->load(['unit', 'building']);
        } else {
          $contract->contractable->load(['building']);
        }

        return new ContractResource($contract);
    }

    public function store(Request $request): ContractResource
    {
        $this->authorize('create', Contract::class);

        $data = $request->validate([
            'contract.id' => ['sometimes', 'nullable', 'integer'],
            'contract.type' => ['required', 'in:Unit,Partition'],
            'contract.contractable_id' => ['required', 'integer'],
            'contract.rent' => ['required', 'numeric'],
            'contract.discount' => ['nullable', 'numeric'],
            'contract.start_date' => ['required', 'date'],
            'contract.end_date' => ['required', 'date'],
            'tenants' => ['required', 'array', 'min:1'],
            'tenants.*.id' => ['sometimes', 'nullable', 'integer'],
            'tenants.*.first_name' => ['required', 'string'],
            'tenants.*.last_name' => ['required', 'string'],
            'tenants.*.gender' => ['required', 'string'],
            'tenants.*.dob' => ['required', 'date'],
            'tenants.*.email' => ['nullable', 'email'],
            'tenants.*.phone' => ['nullable', 'string'],
            'tenants.*.identity_number' => ['nullable', 'string'],
            'tenants.*.identity_expiry' => ['nullable', 'date'],
            'tenants.*.passport_number' => ['nullable', 'string'],
            'tenants.*.passport_expiry' => ['nullable', 'date'],
            'tenants.*.is_holder' => ['required', 'boolean'],
        ]);
        $data['contract']['contractable_type'] = $data['contract']['type'] == 'Unit' ? Unit::class : Partition::class;
        $data['contract']['discount'] = $data['contract']['discount'] ?? 0;

        $contract = DB::transaction(function () use ($data) {
            $tenants = collect($data['tenants']);

            $holder = $tenants->where('is_holder', true)->first();
            $holder['parent_id'] = null;
            $mainTenant = Tenant::updateOrCreate(['tenants.id' => $holder['id']], $holder);

            foreach ($tenants->where('is_holder', false) as $tenant) {
                $mainTenant->subtenants()->updateOrCreate(['tenants.id' => $tenant['id']], $tenant);
            }

            if (isset($data['contract']['id'])) {
                $currentContract = Contract::findOrFail($data['contract']['id']);
                if ($currentContract->end_date > Carbon::today()) {
                    $currentContract->update(['end_date' => Carbon::make($data['contract']['start_date'])->subDay()]);
                }
                unset($data['contract']['id']);
            }

            $contract = $mainTenant->contracts()->create($data['contract']);
            $contract->update([ 'reference' => '#'.str_pad($contract->id, 9, "0", STR_PAD_LEFT) ]);
            return $contract;
        });

        return new ContractResource($contract->load('tenant.subtenants', 'tenant.security_deposit'));
    }

    public function destroy(Contract $contract): Response
    {
        $this->authorize('delete', $contract);

        $contract->delete();
        return response()->noContent();
    }

    public function rentStats(Request $request): Response
    {
        $query = Contract::query();

        if ($keyword = $request->get('keyword')) {
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('reference', 'like', "%$keyword%");
                $q->orWhereHas('tenant', function ($query) use ($keyword) {
                    $query->whereRaw("first_name || ' ' || last_name LIKE '%$keyword%' ");
                });
                $q->orWhereHasMorph('contractable', Unit::class, function ($query) use ($keyword) {
                    $query->where('name', 'like', "%$keyword%");
                    $query->orWhereHas('building', function ($query) use ($keyword) {
                        $query->where('name', 'like', "%$keyword%");
                    });
                });
                $q->orWhereHasMorph('contractable', Partition::class, function ($query) use ($keyword) {
                    $query->where('name', 'like', "%$keyword%");
                    $query->orWhereHas('unit', function ($query) use ($keyword) {
                        $query->where('name', 'like', "%$keyword%");
                        $query->orWhereHas('building', function ($query) use ($keyword) {
                            $query->where('name', 'like', "%$keyword%");
                        });
                    });
                });
            });
        }

        if ($buildingId = $request->get('building_id')) {
            $query->where(function (Builder $q) use ($buildingId) {
                $q->whereHasMorph('contractable', Unit::class, function ($query) use ($buildingId) {
                    $query->where('building_id', $buildingId);
                });
                $q->orWhereHasMorph('contractable', Partition::class, function ($query) use ($buildingId) {
                    $query->whereHas('unit', function ($query) use ($buildingId) {
                        $query->where('building_id', $buildingId);
                    });
                });
            });
        }

        if ($unitId = $request->get('unit_id')) {
            $query->where(function (Builder $q) use ($unitId) {
                $q->whereHasMorph('contractable', Unit::class, function ($query) use ($unitId) {
                    $query->where('id', $unitId);
                });
                $q->orWhereHasMorph('contractable', Partition::class, function ($query) use ($unitId) {
                    $query->where('unit_id', $unitId);
                });
            });
        }

        if ($partitionId = $request->get('partition_id')) {
            $query->whereHasMorph('contractable', Partition::class, function ($query) use ($partitionId) {
                $query->where('id', $partitionId);
            });
        }

        if ($type = $request->get('type')) {
            $query->where('contractable_type', $type == 'Unit' ? Unit::class : Partition::class);
        }

        if ($startDate = $request->get('start_date')) {
            $query->where('start_date', '>=', Carbon::parse($startDate)->subDay());
        }

        if ($endDate = $request->get('end_date')) {
            $query->where('end_date', '<=', Carbon::parse($endDate));
        }

        $tab = $request->get('tab');

        if ($tab == 'active') {
            $query->where('start_date', '<=', Carbon::today());
            $query->where('end_date', '>=', Carbon::today());
        } else if ($tab == 'expired') {
            $query->where('end_date', '<', Carbon::today());
        }

        $contracts = $query->with(['payments'])->get();

        $totalRentDue = $contracts->sum('final_rent');
        $rentPaid = 0;
        foreach($contracts as $contract) {
            $rentPaid += $contract->payments->sum('amount');
        }

        return response([
            'total_due' => $totalRentDue,
            'paid' => $rentPaid,
            'remaining' => $totalRentDue - $rentPaid,
        ]);
    }
}
