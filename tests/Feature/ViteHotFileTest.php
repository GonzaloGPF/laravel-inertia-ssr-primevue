<?php

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Vite;

/**
 * A server on a port that was free.
 *
 * @return array{resource, int} The server, and its port
 */
function listen(): array
{
    $server = stream_socket_server('tcp://127.0.0.1:0');

    return [$server, (int) explode(':', stream_socket_get_name($server, false))[1]];
}

/**
 * A port where nobody listens: one that was just free.
 */
function deadPort(): int
{
    [$server, $port] = listen();
    fclose($server);

    return $port;
}

/**
 * Starts the application with a hot file that has the given content, as the dev server leaves it.
 */
function bootWithHotFile(?string $content): string
{
    $hotFile = sys_get_temp_dir().'/vite-hot-'.uniqid();

    if ($content !== null) {
        file_put_contents($hotFile, $content);
    }

    Vite::useHotFile($hotFile);

    (new AppServiceProvider(app()))->boot();

    return $hotFile;
}

afterEach(function () {
    foreach (glob(sys_get_temp_dir().'/vite-hot-*') as $file) {
        unlink($file);
    }
});

it('ignores the hot file of a dev server that is not there any more', function () {
    $hotFile = bootWithHotFile('http://127.0.0.1:'.deadPort());

    expect(Vite::isRunningHot())->toBeFalse()
        ->and(Vite::hotFile())->not->toBe($hotFile)
        ->and(file_exists($hotFile))->toBeTrue(); // Whatever left it there is not for the application to delete
});

it('keeps the hot file of a dev server that answers', function () {
    [$server, $port] = listen();

    $hotFile = bootWithHotFile("http://127.0.0.1:$port");

    expect(Vite::isRunningHot())->toBeTrue()
        ->and(Vite::hotFile())->toBe($hotFile);

    fclose($server);
});

it('ignores a hot file that has no address', function () {
    bootWithHotFile('not an address');

    expect(Vite::isRunningHot())->toBeFalse();
});

it('has nothing to ignore when there is no hot file', function () {
    $hotFile = bootWithHotFile(null);

    expect(Vite::isRunningHot())->toBeFalse()
        ->and(Vite::hotFile())->toBe($hotFile);
});

it('does not look for the dev server in production', function () {
    app()->detectEnvironment(fn () => 'production');

    $hotFile = bootWithHotFile('http://127.0.0.1:'.deadPort());

    expect(Vite::hotFile())->toBe($hotFile);
});
