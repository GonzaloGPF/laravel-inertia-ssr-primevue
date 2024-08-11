<?php

namespace App\Api;

use App\Jobs\HttpRequestJob;
use App\Responses\SimpleResponse;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

abstract class ApiBase
{
    private ?Response $response = null;

    private PendingRequest $request;

    private array $options;

    private array $headers;

    private bool $active;

    private array $basicAuth;

    private ?string $token;

    private bool $asForm;

    private bool $shouldQueue;

    private array $attachments;

    public function __construct(private readonly SimpleResponse $simpleResponse)
    {
        $this->headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
        $this->options = [];
        $this->basicAuth = [];
        $this->token = '';
        $this->asForm = false;
        $this->active = true;
        $this->shouldQueue = false;
        $this->attachments = [];
        $this->init();
    }

    abstract protected function init();

    public function getOptions(?string $value = null): array|string|float
    {
        if ($value) {
            return $this->options[$value];
        }

        return $this->options;
    }

    public function setOptions(array $options): ApiBase
    {
        $this->options = $options;

        return $this;
    }

    public function setAttachments(string $fieldName, $file): ApiBase
    {
        $this->attachments = [$fieldName, file_get_contents($file), $file->getClientOriginalName()];

        return $this;
    }

    public function getAttachments(): array
    {

        return $this->attachments;
    }

    public function setHeaders(array $options): ApiBase
    {
        $this->headers = array_merge($this->headers, $options);

        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBasicAuth(): array
    {
        return $this->basicAuth;
    }

    public function setBasicAuth(string $username, string $password): ApiBase
    {
        $this->basicAuth = [$username, $password];

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(?string $token): ApiBase
    {
        $this->token = $token;

        return $this;
    }

    public function getAsForm(): bool
    {
        return $this->asForm;
    }

    public function setAsForm(): ApiBase
    {
        $this->asForm = true;
        unset($this->headers['Content-Type']);
        unset($this->headers['Accept']);

        return $this;
    }

    /**
     * @return ?Response
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function setShouldQueue(bool $value): void
    {
        $this->shouldQueue = $value;
    }

    public function isOk(): bool
    {
        $response = $this->getResponse();

        if (! $response) {
            return false;
        }

        return ! $response->serverError()
            && ! $response->clientError();
    }

    public function get(string $url, array $options = []): array
    {
        return $this->makeRequest('get', $url, $options);
    }

    public function put(string $url, array $data = []): array
    {
        return $this->makeRequest('put', $url, $data);
    }

    public function patch(string $url, array $data = []): array
    {
        return $this->makeRequest('patch', $url, $data);
    }

    public function post(string $url, array $data = []): array
    {
        return $this->makeRequest('post', $url, $data);
    }

    public function delete(string $url, array $data = []): array
    {
        return $this->makeRequest('delete', $url, $data);
    }

    private function makeRequest(string $verb, string $url, array $data = []): array
    {
        if ($this->shouldQueue) {
            HttpRequestJob::dispatch($this->getConfig(), $verb, $url, $data);
            return [];
        }
        try {
            $response = $this->prepareRequest()
                ->doRequest($verb, $url, $data)
                ->json() ?? [];
        } catch (Exception $exception) {
            logger('Api Error', ['message' => $exception->getTraceAsString()]);

            return [];
        }

        if ($this->response->clientError()) {
            return $this->simpleResponse->json(trans('help.whoops'), $response);
        }

        if ($this->response->serverError()) {
            $message = trans('exceptions.server_error');

            return $this->simpleResponse->json($message, $response);
        }

        if (! is_array($response)) {
            $response = ['message' => $response];
        }

        return $response;
    }

    public function prepareRequest(): ApiBase
    {
        $this->request = Http::withOptions($this->options);
        //->withHeaders($this->headers);

        if (! empty($this->basicAuth)) {
            $this->request->withBasicAuth($this->basicAuth[0], $this->basicAuth[1]);
        }

        if (! empty($this->token)) {
            $this->request->withToken($this->token);
        }

        if (! empty($this->attachments)) {
            $this->request->attach($this->attachments[0], $this->attachments[1], $this->attachments[2]);
        }

        if ($this->asForm) {
            $this->request->asForm();
        }

        return $this;
    }

    /**
     * @throws BindingResolutionException
     */
    public function doRequest(string $verb, string $url, array $data): Response
    {
        $this->response = $this->canMakeRequest()
            ? $this->request->$verb($url, $data)
            : $this->fakeResponse($url);
        $this->doLog($verb, $url, $data);

        return $this->response;
    }

    /**
     * It will log correctly depending on environments
     * If its on live, it will throw exceptions logs
     * if its local or testing, it will just log in file
     */
    private function doLog(string $verb, string $path, array $data = []): void
    {
        if (! Str::startsWith('/', $path)) {
            $path = '/'.$path;
        }

        $fullUrl = ($this->getOptions()['base_uri'] ?? '').$path;

        $data = [
            'verb' => $verb,
            'url' => $fullUrl,
            'path' => parse_url($fullUrl, PHP_URL_PATH),
            'query' => parse_url($fullUrl, PHP_URL_QUERY),
            'headers' => $this->getHeaders(),
            'body' => $data,
            'config' => $this->getConfig(),
            'outgoing' => true,
            'response' => $this->response->json(),
            'code' => $this->response->status(),
        ];
    }

    public function getConfig(): array
    {
        return [
            'auth' => $this->basicAuth,
            'options' => $this->options,
            'token' => $this->token,
            'asForm' => $this->asForm,
            'canMakeRequest' => $this->canMakeRequest(),
        ];
    }

    private function canMakeRequest(): bool
    {
        return $this->active;
    }

    private function fakeResponse(string $url): Response
    {
        try {
            $body = File::json(base_path('tests/mocks/ok.json'));
        } catch (FileNotFoundException $e) {
            error_log($e->getMessage());
            $body = '';
        }

        return Http::fake(['*' => Http::response($body)])->get($url);
    }
}
