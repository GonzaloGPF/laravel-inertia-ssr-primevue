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
