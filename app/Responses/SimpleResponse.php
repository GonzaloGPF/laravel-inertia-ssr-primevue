<?php

namespace App\Responses;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;

class SimpleResponse
{
    /**
     * Builds up app's json response structure
     *
     * @param  null  $data
     */
    public function json(string $message, $data = null): array
    {
        return [
            'message' => $message,
            'data' => $data,
        ];
    }

    /**
     * Returns a json response that can includes specific data.
     */
    public function jsonResponse(string $message, array $data = [], int $code = Response::HTTP_OK): JsonResponse
    {
        $data = array_merge(['message' => $message], $data);

        return response()->json($data, $code);
    }

    public function plainResponse(?string $content = null): Response
    {
        return response($content)->header('Content-Type', 'text/plain');
    }

    /**
     * Builds up a json response using an Api Resource.
     */
    public function resource(string $message, JsonResource $resource, int $code): JsonResponse
    {
        return $resource->additional(array_merge(compact('code', 'message'), $resource->additional))
            ->response()
            ->setStatusCode($code);
    }

    public function empty(): JsonResponse
    {
        return $this->jsonResponse(trans('help.no_content'), null, Response::HTTP_NO_CONTENT);
    }

    public function exception(Exception|string $exception, int $code = Response::HTTP_FORBIDDEN, ?array $data = []): JsonResponse
    {
        if ($exception instanceof Exception) {
            $message = $exception->getMessage();
            if ($message === 'This action is unauthorized.') {
                $message = trans('exceptions.unauthorized');
            }
        } elseif (Lang::has("exceptions.$exception")) {
            $message = trans("exceptions.$exception");
        } else {
            $message = $exception ?? trans('exceptions.server_error');
        }

        return $this->jsonResponse($message, $data, $code);
    }
}
