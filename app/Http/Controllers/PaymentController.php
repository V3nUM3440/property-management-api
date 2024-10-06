<?php

namespace App\Http\Controllers;

use App\Http\Resources\Core\AppAnonymousResourceCollection;
use App\Http\Resources\PaymentResource;
use App\Models\Partition;
use App\Models\Payment;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentController extends Controller
{
    public function index(Request $request): AppAnonymousResourceCollection
    {
        $this->authorize('viewAny', Payment::class);

        $query = Payment::query();

        if ($keyword = $request->get('keyword')) {
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('reference', 'like', "%$keyword%");
                $q->orWhereHas('contract', function ($query) use ($keyword) {
                    $query->where('reference', 'like', "%$keyword%");
                    $query->orWhereHasMorph('contractable', Unit::class, function ($query) use ($keyword) {
                        $query->where('name', 'like', "%$keyword%");
                        $query->orWhereHas('building', function ($query) use ($keyword) {
                            $query->where('name', 'like', "%$keyword%");
                        });
                    });
                    $query->orWhereHasMorph('contractable', Partition::class, function ($query) use ($keyword) {
                        $query->where('name', 'like', "%$keyword%");
                        $query->orWhereHas('unit', function ($query) use ($keyword) {
                            $query->where('name', 'like', "%$keyword%");
                            $query->orWhereHas('building', function ($query) use ($keyword) {
                                $query->where('name', 'like', "%$keyword%");
                            });
                        });
                    });
                });
            });
        }

        if ($buildingId = $request->get('building_id')) {
            $query->whereHas('contract', function (Builder $q) use ($buildingId) {
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
            $query->whereHas('contract', function (Builder $q) use ($unitId) {
                $q->whereHasMorph('contractable', Unit::class, function ($query) use ($unitId) {
                    $query->where('id', $unitId);
                });
                $q->orWhereHasMorph('contractable', Partition::class, function ($query) use ($unitId) {
                    $query->where('unit_id', $unitId);
                });
            });
        }

        if ($partitionId = $request->get('partition_id')) {
            $query->whereHas('contract', function ($q) use ($partitionId) {
                $q->whereHasMorph('contractable', Partition::class, function ($query) use ($partitionId) {
                    $query->where('id', $partitionId);
                });
            });
        }

        if ($startDate = $request->get('start_date')) {
            $query->where('datetime', '>=', Carbon::parse($startDate));
        }

        if ($endDate = $request->get('end_date')) {
            $query->where('datetime', '<=', Carbon::parse($endDate));
        }

        $payments = $query->with(['contract'])
        ->orderBy($request->order_by ?? 'datetime', $request->order_dir ?? 'desc')
        ->paginate($request->per_page ?? 15);

        return PaymentResource::collection($payments);
    }

    public function store(Request $request): PaymentResource
    {
        $data = $request->validate([
            'contract_id' => ['required', 'exists:contracts,id'],
            'amount' => ['required', 'numeric'],
            'method' => ['required', 'string'],
            'status' => ['required', 'in:pending,paid'],
            'datetime' => ['required', 'date'],
        ]);

        $payment = Payment::create($data);
        $payment->update([ 'reference' => '#'.str_pad($payment->id, 9, "0", STR_PAD_LEFT) ]);

        return new PaymentResource($payment);
    }

    public function show(Payment $payment): PaymentResource
    {
        $payment->load('contract.contractable');
        if ($payment->contract->contractable_type == Partition::class) {
            $payment->contract->contractable->load(['unit', 'building']);
        } else {
          $payment->contract->contractable->load(['building']);
        }

        return new PaymentResource($payment);
    }

    public function update(Request $request, Payment $payment): PaymentResource
    {
        $data = $request->validate([
            'amount' => ['sometimes', 'numeric'],
            'method' => ['sometimes', 'string'],
            'status' => ['sometimes', 'in:pending,paid'],
            'datetime' => ['sometimes', 'date'],
        ]);

        $payment->update($data);

        return new PaymentResource($payment);
    }

    public function destroy(Payment $payment): Response
    {
        $payment->delete();
        return response()->noContent();
    }
}
