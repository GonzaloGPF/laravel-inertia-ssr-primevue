<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(TestCase::class, RefreshDatabase::class)->in('Feature');
uses(TestCase::class, RefreshDatabase::class)->in('Browser');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Waits for the table to render the given amount of rows, as it loads asynchronously.
 */
function expectRows($page, int $expected): void
{
    $count = 0;

    for ($attempt = 0; $attempt < 50; $attempt++) {
        $count = (int) $page->script("document.querySelectorAll('.p-datatable-tbody > tr').length");

        if ($count === $expected) {
            break;
        }

        usleep(100_000);
    }

    expect($count)->toBe($expected);
}

/**
 * Selector of the row action button. Users are listed from the newest to the oldest.
 */
function rowAction(int $row, string $action): string
{
    return ".p-datatable-tbody > tr:nth-child($row) button[aria-label=\"$action\"]";
}

/**
 * The text of the information shown in a container (by default, the expanded rows of the table), as 'Label|Value' pairs.
 * The initials of an avatar without image are not information, so they are left out.
 */
function infoPairs($page, int $position = 0, string $container = '.p-datatable-row-expansion'): string
{
    return (string) $page->script(<<<JS
        (() => {
            const element = [...document.querySelectorAll('$container')][$position]
            const initials = element?.querySelector('.p-avatar')?.innerText.trim()
            const lines = element?.innerText.split('\\n').map(line => line.trim()).filter(Boolean) ?? []

            return (initials && lines[0] === initials ? lines.slice(1) : lines).join('|')
        })()
    JS);
}

/**
 * An image in a file the browser can upload, named as the user would have it.
 */
function imageToUpload(string $name = 'avatar.png'): string
{
    $path = sys_get_temp_dir().'/'.$name;

    imagepng(imagecreatetruecolor(64, 64), $path);

    return $path;
}

/**
 * Starts to record the requests the page sends. The server of the browser tests does not read multipart
 * requests, so what the files do in the backend is tested in the feature tests, and here what the page sends.
 */
function recordRequests($page): void
{
    $page->script(<<<'JS'
        (() => {
            window.__requests = []
            const open = XMLHttpRequest.prototype.open
            const send = XMLHttpRequest.prototype.send
            XMLHttpRequest.prototype.open = function (method, url) {
                this.__info = { method, url }
                return open.apply(this, arguments)
            }
            XMLHttpRequest.prototype.send = function (body) {
                const fields = body instanceof FormData
                    ? Object.fromEntries([...body.entries()].map(([key, value]) => [key, value instanceof File ? 'file:' + value.name : value]))
                    : body
                window.__requests.push({ ...this.__info, body: fields })
                return send.apply(this, arguments)
            }
        })()
    JS);
}

/**
 * The first request recorded that is not a GET, waiting for the page to send it.
 */
function sentRequest($page): array
{
    $requests = [];

    for ($attempt = 0; $attempt < 50; $attempt++) {
        $requests = json_decode($page->script("JSON.stringify(window.__requests.filter(request => request.method !== 'GET'))"), true);

        if ($requests) {
            break;
        }

        usleep(100_000);
    }

    expect($requests)->not->toBeEmpty();

    return $requests[0];
}
