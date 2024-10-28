<?php

namespace App\Http\Controllers;

use App\Http\Resources\BuildingResource;
use App\Http\Resources\Core\AppAnonymousResourceCollection;
use App\Models\Building;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BuildingController extends Controller
{
    public function index(Request $request): AppAnonymousResourceCollection
    {
        $query = Building::query();

        if ($keyword = $request->get('keyword')) {
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%");
                $q->orWhere('type', 'like', "%$keyword%");
            });
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($request->per_page == 'all') {
            return BuildingResource::collection($query->select(['id', 'name'])->get());
        }

        $this->authorize('viewAny', Building::class);
        $buildings = $query->withCount(['units'])
        ->orderBy($request->order_by ?? 'name', $request->order_dir ?? 'asc')
        ->paginate($request->per_page ?? 15);
        return BuildingResource::collection($buildings);
    }

    public function show(Building $building): BuildingResource
    {
        $this->authorize('view', $building);
        return new BuildingResource($building->loadCount(['units']));
    }

    public function store(Request $request): BuildingResource
    {
        $this->authorize('create', Building::class);

        $data = $request->validate([
            'name' => ['required', 'string'],
            'type' => ['required', 'in:residential,commercial'],
            'size' => ['numeric', 'nullable'],
            'address' => ['string', 'nullable'],
            'city' => ['string', 'nullable'],
            'state' => ['string', 'nullable'],
            'country' => ['string', 'nullable'],
        ]);

        $building = Building::create($data);
        return new BuildingResource($building);
    }

    public function update(Request $request, Building $building): BuildingResource
    {
        $this->authorize('update', $building);

        $data = $request->validate([
            'name' => ['required', 'string'],
            'type' => ['required', 'in:residential,commercial'],
            'size' => ['numeric', 'nullable'],
            'address' => ['string', 'nullable'],
            'city' => ['string', 'nullable'],
            'state' => ['string', 'nullable'],
            'country' => ['string', 'nullable'],
        ]);

        $building->update($data);
        return new BuildingResource($building);
    }

    public function destroy(Building $building): Response
    {
        $this->authorize('delete', $building);

        foreach($building->units as $unit) {
            Payment::whereIn('contract_id', $unit->contracts()->pluck('id'))->delete();
            $unit->contracts()->forceDelete();
            $unit->security_deposits()->delete();
        }
        foreach($building->partitions as $partition) {
            Payment::whereIn('contract_id', $partition->contracts()->pluck('id'))->delete();
            $partition->contracts()->forceDelete();
            $partition->security_deposits()->delete();
        }
        $building->delete();

        return response()->noContent();
    }
}
