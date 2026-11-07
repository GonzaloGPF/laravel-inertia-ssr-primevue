<?php

namespace App\Http\Controllers;

use App\Responses\SimpleResponse;
use App\Traits\IsFilterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class AutoCompleteController extends Controller
{
    public function __construct(private readonly SimpleResponse $response) {}

    /**
     * The models of the autocompletes are the filterable ones, and each one is up to its policy: a model
     * without a policy is not for anybody.
     */
    public function index(string $model): JsonResponse
    {
        $class = modelByString($model);

        abort_unless(
            $class && class_exists($class) && in_array(IsFilterable::class, class_uses_recursive($class), true),
            Response::HTTP_NOT_FOUND
        );

        Gate::authorize('viewAny', $class);

        /** @var LengthAwarePaginator<int, Model> $data */
        $data = resolve($class)::filter(request()->all())->getOrPaginate();

        return $this->response->jsonResponse(tAction('index', $class), $data->toArray());
    }
}
