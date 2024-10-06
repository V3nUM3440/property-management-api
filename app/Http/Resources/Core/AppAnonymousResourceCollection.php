<?php

namespace App\Http\Resources\Core;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AppAnonymousResourceCollection extends AnonymousResourceCollection
{
    public function paginationInformation($request, $paginated, $default): array
    {
        return [
            'pagination' => [
                'current_page' => $paginated['current_page'],
                'from' => $paginated['from'],
                'to' => $paginated['to'],
                'total' => $paginated['total'],
                'per_page' => $paginated['per_page'],
                'last_page' => $paginated['last_page'],
            ],
        ];
    }
}
