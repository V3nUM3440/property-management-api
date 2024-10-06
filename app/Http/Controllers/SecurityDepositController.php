<?php

namespace App\Http\Controllers;

use App\Http\Resources\Core\AppAnonymousResourceCollection;
use App\Http\Resources\SecurityDepositResource;
use App\Models\Partition;
use App\Models\SecurityDeposit;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SecurityDepositController extends Controller
{
    public function index(Request $request): AppAnonymousResourceCollection
    {
        $query = SecurityDeposit::query();

        if ($keyword = $request->get('keyword')) {
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('reference', 'like', "%$keyword%");
                $q->orWhereHas('tenant', function ($query) use ($keyword) {
                    $query->whereRaw("first_name || ' ' || last_name LIKE '%$keyword%' ");
                });
                $q->orWhereHasMorph('depositable', Unit::class, function ($query) use ($keyword) {
                    $query->where('name', 'like', "%$keyword%");
                    $query->orWhereHas('building', function ($query) use ($keyword) {
                        $query->where('name', 'like', "%$keyword%");
                    });
                });
                $q->orWhereHasMorph('depositable', Partition::class, function ($query) use ($keyword) {
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
                $q->whereHasMorph('depositable', Unit::class, function ($query) use ($buildingId) {
                    $query->where('building_id', $buildingId);
                });
                $q->orWhereHasMorph('depositable', Partition::class, function ($query) use ($buildingId) {
                    $query->whereHas('unit', function ($query) use ($buildingId) {
                        $query->where('building_id', $buildingId);
                    });
                });
            });
        }

        if ($unitId = $request->get('unit_id')) {
            $query->where(function (Builder $q) use ($unitId) {
                $q->whereHasMorph('depositable', Unit::class, function ($query) use ($unitId) {
                    $query->where('id', $unitId);
                });
                $q->orWhereHasMorph('depositable', Partition::class, function ($query) use ($unitId) {
                    $query->where('unit_id', $unitId);
                });
            });
        }

        if ($partitionId = $request->get('partition_id')) {
            $query->whereHasMorph('depositable', Partition::class, function ($query) use ($partitionId) {
                $query->where('id', $partitionId);
            });
        }

        if ($startDate = $request->get('start_date')) {
            $query->where('return_date', '>=', Carbon::parse($startDate)->subDay());
        }

        if ($endDate = $request->get('end_date')) {
            $query->where('return_date', '<=', Carbon::parse($endDate));
        }

        $this->authorize('viewAny', SecurityDeposit::class);
        $deposits = $query->with(['tenant', 'depositable'])
        ->orderBy($request->order_by ?? 'created_at', $request->order_dir ?? 'desc')
        ->paginate($request->per_page ?? 15);

        $deposits->loadMorph('depositable', [
            Unit::class => ['building'],
            Partition::class => ['unit', 'building'],
        ]);

        return SecurityDepositResource::collection($deposits);
    }

    public function show(SecurityDeposit $security_deposit): SecurityDepositResource
    {
        $this->authorize('view', $security_deposit);

        $security_deposit->load(['depositable', 'tenant']);

        if ($security_deposit->depositable_type == Partition::class) {
            $security_deposit->depositable->load(['unit', 'building']);
        } else {
          $security_deposit->depositable->load(['building']);
        }

        return new SecurityDepositResource($security_deposit);
    }

    public function store(Request $request): SecurityDepositResource
    {
        $this->authorize('create', SecurityDeposit::class);

        $data = $request->validate([
            'type' => ['required', 'in:Unit,Partition'],
            'depositable_id' => ['required', 'integer'],
            'tenant_id' => ['required', 'exists:tenants,id'],
            'amount' => ['required', 'numeric'],
            'receive_date' => ['required', 'date'],
            'return_date' => ['nullable', 'date'],
        ]);
        $data['depositable_type'] = $data['type'] == 'Unit' ? Unit::class : Partition::class;

        $deposit = SecurityDeposit::create($data);
        return new SecurityDepositResource($deposit);
    }

    public function update(Request $request, SecurityDeposit $security_deposit): SecurityDepositResource
    {
        $this->authorize('update', $security_deposit);

        $data = $request->validate([
            'type' => ['sometimes', 'in:Unit,Partition'],
            'depositable_id' => ['required', 'integer'],
            'tenant_id' => ['sometimes', 'exists:tenants,id'],
            'amount' => ['sometimes', 'numeric'],
            'receive_date' => ['sometimes', 'date'],
            'return_date' => ['sometimes', 'nullable', 'date'],
        ]);
        $data['depositable_type'] = $data['type'] == 'Unit' ? Unit::class : Partition::class;

        $security_deposit->update($data);
        return new SecurityDepositResource($security_deposit);
    }

    public function destroy(SecurityDeposit $security_deposit): Response
    {
        $this->authorize('delete', $security_deposit);

        $security_deposit->delete();
        return response()->noContent();
    }
}
