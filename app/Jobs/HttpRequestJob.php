<?php

namespace App\Jobs;

use App\Api\ApiBase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HttpRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var array<string, mixed> */
    private array $options;

    private string $verb;

    private string $url;

    /** @var array<string, mixed> */
    private array $data;

    /**
     * Create a new job instance.
     *
     * @param  array<string, mixed>  $options
     * @param  array<string, mixed>  $data
     */
    public function __construct(array $options, string $verb, string $url, array $data = []) // TODO: test it
    {
        $this->options = $options;
        $this->verb = $verb;
        $this->url = $url;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @throws BindingResolutionException
     */
    public function handle(ApiBase $service): void
    {
        $service->setOptions($this->options);

        $service->prepareRequest()
            ->doRequest($this->verb, $this->url, $this->data);
    }
}
