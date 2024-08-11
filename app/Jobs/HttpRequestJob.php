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

    private array $options;
    private string $verb;
    private string $url;
    private array $data;

    /**
     * Create a new job instance.
     *
     * @param array $options
     * @param string $verb
     * @param string $url
     * @param array $data
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
     * @param ApiBase $service
     * @return void
     * @throws BindingResolutionException
     */
    public function handle(ApiBase $service): void
    {
        $service->setOptions($this->options);

        $service->prepareRequest()
            ->doRequest($this->verb, $this->url, $this->data);
    }
}
