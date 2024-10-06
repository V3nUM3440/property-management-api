<?php

namespace App\Http\Controllers;

use App\Http\Resources\Core\AppAnonymousResourceCollection;
use App\Http\Resources\PartitionResource;
use App\Models\Partition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PartitionController extends Controller
{
    public function index(Request $request): AppAnonymousResourceCollection
    {
        if ($request->user()->can('view units others')) {
            $query = Partition::query();
        } else {
            $query = Partition::whereIn('unit_id', $request->user()->units()->pluck('id'));
        }

        if ($keyword = $request->get('keyword')) {
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%");
                $q->orWhereHas('unit', function ($query) use ($keyword) {
                    $query->where('name', 'like', "%$keyword%");
                    $query->orWhereHas('building', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%$keyword%");
                    });
                });
            });
        }

        if ($buildingId = $request->get('building_id')) {
            $query->whereHas('unit', function ($query) use ($buildingId) {
                $query->whereHas('building', function ($q) use ($buildingId) {
                    $q->where('id', "$buildingId");
                });
            });
        }

        if ($unitId = $request->get('unit_id')) {
            $query->where('unit_id', $unitId);
        }

        if ($occupancy = $request->get('occupancy')) {
            if ($occupancy == 'occupied') {
                $query->has('active_contract');
            } else {
                $query->doesntHave('active_contract');
            }
        }

        if ($request->per_page == 'all') {
            return PartitionResource::collection($query->select(['id', 'name'])->get());
        }

        $this->authorize('viewAny', Partition::class);
        $partitions = $query->with(['unit', 'building', 'active_contract'])
        ->orderBy($request->order_by ?? 'name', $request->order_dir ?? 'asc')
        ->paginate($request->per_page ?? 15);
        return PartitionResource::collection($partitions);
    }

    public function show(Partition $partition): PartitionResource
    {
        $this->authorize('view', $partition);

        $partition->load(['unit', 'building', 'contracts' => function ($query) {
            $query->withTrashed();
        }, 'contracts.tenant', 'contracts.payments', 'security_deposits.tenant']);

        return new PartitionResource($partition);
    }

    public function store(Request $request): PartitionResource
    {
        $this->authorize('create', Partition::class);

        $data = $request->validate([
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'name' => ['string', 'required'],
            'size' => ['numeric', 'nullable'],
        ]);

        $partition = Partition::create($data);
        return new PartitionResource($partition);
    }

    public function update(Request $request, Partition $partition): PartitionResource
    {
        $this->authorize('update', $partition);

        $data = $request->validate([
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'name' => ['string', 'required'],
            'size' => ['numeric', 'nullable'],
        ]);

        $partition->update($data);
        return new PartitionResource($partition);
    }

    public function destroy(Partition $partition): Response
    {
        $this->authorize('delete', $partition);

        $partition->contracts()->delete();
        $partition->delete();
        return response()->noContent();
    }
}
