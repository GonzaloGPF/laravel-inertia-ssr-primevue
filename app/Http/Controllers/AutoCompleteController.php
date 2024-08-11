<?php

namespace App\Http\Controllers;

use App\Responses\SimpleResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class AutoCompleteController extends Controller
{
    public function __construct(private readonly SimpleResponse $response)
    {
    }

    public function index(string $model): JsonResponse
    {
        $model = resolve(modelByString($model));

        /** @var LengthAwarePaginator $data */
        $data = $model::filter(request()->all())->getOrPaginate();

        return $this->response->jsonResponse(tAction('index', $model), $data->toArray());
    }
}
