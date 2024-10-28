<?php

namespace App\Http\Controllers;

use App\Http\Resources\Core\AppAnonymousResourceCollection;
use App\Http\Resources\UnitResource;
use App\Models\Payment;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UnitController extends Controller
{
    public function index(Request $request): AppAnonymousResourceCollection
    {
        if ($request->user()->can('view units others')) {
            $query = Unit::query();
        } else {
            $query = $request->user()->units();
        }

        if ($keyword = $request->get('keyword')) {
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%");
                $q->orWhere('floor', 'like', "%$keyword%");
                $q->orWhereHas('building', function ($query) use ($keyword) {
                    $query->where('name', 'like', "%$keyword%");
                });
                $q->orWhereHas('manager', function ($query) use ($keyword) {
                    $query->where('name', 'like', "%$keyword%");
                });
            });
        }

        if ($buildingId = $request->get('building_id')) {
            $query->where('building_id', $buildingId);
        }

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($occupancy = $request->get('occupancy')) {
            if ($occupancy == 'occupied') {
                $query->where(function ($query) {
                    $query->has('active_contract');
                    $query->orWhereHas('partitions', function ($query) {
                        $query->has('active_contract');
                    });
                });
            } else {
                $query->where(function (Builder $query) {
                    $query->doesntHave('active_contract');
                    $query->where(function (Builder $q) {
                        $q->doesntHave('partitions');
                        $q->orWhereHas('partitions', function (Builder $query) {
                            $query->doesntHave('active_contract');
                        });
                    });
                });
            }
        }

        if ($request->per_page == 'all') {
            return UnitResource::collection($query->select(['id', 'name'])->get());
        }

        $this->authorize('viewAny', Unit::class);
        $units = $query->with(['building', 'manager', 'active_contract', 'partitions' => function ($query) {
            $query->has('active_contract');
        }])
        ->withCount(['contracts', 'partitions'])
        ->orderBy($request->order_by ?? 'name', $request->order_dir ?? 'asc')
        ->paginate($request->per_page ?? 15);
        return UnitResource::collection($units);
    }

    public function show(Unit $unit): UnitResource
    {
        $this->authorize('view', $unit);

        $unit->load(['building', 'partitions.active_contract', 'manager', 'contracts' => function ($query) {
            $query->withTrashed();
        }, 'contracts.tenant', 'contracts.payments', 'security_deposits.tenant']);

        return new UnitResource($unit);
    }

    public function store(Request $request): UnitResource
    {
        $this->authorize('create', Unit::class);

        $data = $request->validate([
            'building_id' => ['required', 'integer', 'exists:buildings,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'name' => ['required', 'string'],
            'floor' => ['required', 'string'],
            'bedrooms' => ['integer', 'nullable'],
            'bathrooms' => ['integer', 'nullable'],
            'size' => ['numeric', 'nullable'],
        ]);

        $unit = Unit::create($data);
        return new UnitResource($unit);
    }

    public function update(Request $request, Unit $unit): UnitResource
    {
        $this->authorize('update', $unit);

        $data = $request->validate([
            'building_id' => ['required', 'integer', 'exists:buildings,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'name' => ['required', 'string'],
            'floor' => ['required', 'string'],
            'bedrooms' => ['integer', 'nullable'],
            'bathrooms' => ['integer', 'nullable'],
            'size' => ['numeric', 'nullable'],
        ]);

        $unit->update($data);
        return new UnitResource($unit);
    }

    public function destroy(Unit $unit): Response
    {
        $this->authorize('delete', $unit);

        Payment::whereIn('contract_id', $unit->contracts()->pluck('id'))->delete();
        $unit->contracts()->forceDelete();
        $unit->security_deposits()->delete();
        $unit->delete();
        return response()->noContent();
    }
}
