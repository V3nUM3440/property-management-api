<?php

namespace App\Http\Controllers;

use App\Http\Resources\ContractResource;
use App\Http\Resources\Core\AppAnonymousResourceCollection;
use App\Models\Building;
use App\Models\Contract;
use App\Models\Partition;
use App\Models\Tenant;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Response;

class DashboardController extends Controller
{
    public function counts(): Response {
        return response([
            'buildings' => Building::count(),
            'units' => Unit::count(),
            'partitions' => Partition::count(),
            'contracts_units' => Contract::where('contractable_type', Unit::class)->count(),
            'contracts_partitions' => Contract::where('contractable_type', Partition::class)->count(),
            'tenants' => Tenant::count(),
        ]);
    }

    public function occupancyUnits(): Response {
        $buildings = Building::select(['id', 'name'])->withCount('units')->get();

        foreach ($buildings as $building) {
            $building->occupied_units = Unit::where('building_id', $building->id)
                ->where(function ($query) {
                    $query->has('active_contract');
                    $query->orWhereHas('partitions', function ($query) {
                        $query->has('active_contract');
                    });
                }
            )->count();
        }

        $buildings = $buildings->map(function ($building) {
            return [
                'name' => $building->name,
                'occupied_units' => $building->occupied_units,
                'unoccupied_units' => $building->units_count - $building->occupied_units
            ];
        });

        return response($buildings);
    }

    public function occupancyPartitions(): Response {
        $buildings = Building::select(['id', 'name'])->withCount('partitions')->get();

        foreach ($buildings as $building) {
            $building->occupied_partitions = Partition::where(function ($query) {
              $query->has('active_contract');
            })
            ->whereHas('building', function ($query) use ($building) {
              $query->where('buildings.id', $building->id);
            })->count();
        }
        
        $buildings = $buildings->map(function ($building) {
            return [
                'name' => $building->name,
                'occupied_partitions' => $building->occupied_partitions,
                'unoccupied_partitions' => $building->partitions_count - $building->occupied_partitions
            ];
        });

        return response($buildings);
    }

    public function contractsEnding(): AppAnonymousResourceCollection {
        $contracts = Contract::where('end_date', '>', Carbon::today()->subDay())->orderBy('end_date')->take(5)->get();
        $contracts->loadMorph('contractable', [
            Unit::class => ['building'],
            Partition::class => ['unit', 'building'],
        ]);

        return ContractResource::collection($contracts);
    }

    // upcoming payments to collect
    // public function upcomingPayments(): AppAnonymousResourceCollection {
        
    // }
}
