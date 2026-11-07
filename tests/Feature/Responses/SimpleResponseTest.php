<?php

use App\Responses\SimpleResponse;

it('answers with no content when it is empty', function () {
    $response = app(SimpleResponse::class)->empty();

    expect($response->getStatusCode())->toBe(204)
        ->and($response->getData(true))->toHaveKey('message');
});
