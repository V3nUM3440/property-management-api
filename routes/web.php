<?php

use App\Http\Resources\ContractResource;
use App\Http\Resources\PartitionResource;
use App\Http\Resources\TenantResource;
use App\Http\Resources\UserResource;
use App\Models\Building;
use App\Models\Contract;
use App\Models\Partition;
use App\Models\SecurityDeposit;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

  $deposit = SecurityDeposit::with(['depositable'])->find(1);
  return $deposit;

  // $arr = [['name' => 1],['name' => 2],['name' => 3],['name' => 4],['name' => 5]];
  // return collect($arr)->where('name', '>', 3);

  return [
    'message' => 'Welcome to the API',
  ];
});
