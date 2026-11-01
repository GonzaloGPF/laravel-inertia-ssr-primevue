<?php

use App\Enums\Languages;
use App\Models\User;

const LOADING_BAR = '.p-progressbar';

/**
 * What the loading bar is like now: if it is shown, the space it takes, and where the page starts.
 *
 * @return array{shown: bool, height: int, content: int}
 */
function loadingBar($page): array
{
    return json_decode((string) $page->script(<<<'JS'
        (() => {
            const bar = document.querySelector('.p-progressbar')

            return JSON.stringify({
                shown: !!bar && getComputedStyle(bar).visibility !== 'hidden',
                height: Math.round(bar?.parentElement.getBoundingClientRect().height ?? 0),
                content: Math.round(document.querySelector('main').getBoundingClientRect().top),
            })
        })()
    JS), true);
}

/**
 * Waits for the page to have its deferred statistics, and for the loading bar to have had the time to show up.
 */
function waitForDashboard($page): void
{
    // The page is rendered (its cards are there) and none of them is a skeleton any more
    $loaded = fn () => (int) $page->script("document.querySelectorAll('[data-stat-card]').length") === 4
        && (int) $page->script("document.querySelectorAll('.p-skeleton').length") === 0;

    for ($attempt = 0; $attempt < 100 && ! $loaded(); $attempt++) {
        usleep(100_000);
    }

    usleep(600_000); // The bar waits 250 ms to show, so a late one would be there by now
}

/**
 * Holds back the requests of the deferred props for a while, so what is shown while they are loading can be seen.
 */
function holdDeferredProps($page, int $milliseconds): void
{
    $page->script(str_replace('__MILLISECONDS__', (string) $milliseconds, <<<'JS'
        (() => {
            const setRequestHeader = XMLHttpRequest.prototype.setRequestHeader
            const send = XMLHttpRequest.prototype.send

            XMLHttpRequest.prototype.setRequestHeader = function (name, value) {
                if (name.toLowerCase() === 'x-inertia-partial-data') this.__deferred = true

                return setRequestHeader.call(this, name, value)
            }
            XMLHttpRequest.prototype.send = function (...args) {
                return this.__deferred ? setTimeout(() => send.apply(this, args), __MILLISECONDS__) : send.apply(this, args)
            }
        })()
    JS));
}

beforeEach(function () {
    $this->admin = User::factory()->admin()->create(['name' => 'Ada Admin', 'language' => Languages::EN]);

    $this->actingAs($this->admin);
});

test('the loading bar is hidden, but keeps its space, when nothing is loading', function () {
    $page = visit(route('dashboard.show', absolute: false));

    waitForDashboard($page);

    $bar = loadingBar($page);

    expect($bar['shown'])->toBeFalse()
        ->and($bar['height'])->toBe(6);

    $page->assertNoJavaScriptErrors();
});

test('the loading bar is hidden when visits that overlap end before it had the time to show', function () {
    $page = visit('/about');

    // What Inertia does with the deferred props in groups: the visits start together, and end almost together
    $page->script(<<<'JS'
        (() => {
            const fire = (type, visit) => document.dispatchEvent(new CustomEvent(`inertia:${type}`, { detail: { visit } }))
            const visit = () => ({ completed: true, interrupted: false, cancelled: false })
            const [first, second] = [visit(), visit()]

            fire('start', first)
            fire('start', second)
            setTimeout(() => {
                fire('finish', first)
                fire('finish', second)
            }, 50)
        })()
    JS);

    usleep(800_000); // The bar waits 250 ms to show: a late one would be there by now

    expect(loadingBar($page)['shown'])->toBeFalse();

    $page->assertNoJavaScriptErrors();
});

test('the loading bar shows while something is loading, then it is hidden again, and the page never moves', function () {
    $page = visit('/about');

    $idle = loadingBar($page);

    holdDeferredProps($page, 2500);

    $page->click('Ada Admin')
        ->click('li[aria-label="Dashboard"]')
        ->assertRoute('dashboard.show');

    $shown = [];

    for ($attempt = 0; $attempt < 20; $attempt++) {
        $shown[] = loadingBar($page);

        if (end($shown)['shown']) {
            break;
        }

        usleep(100_000);
    }

    expect(end($shown)['shown'])->toBeTrue();

    waitForDashboard($page);

    $after = loadingBar($page);

    expect($after['shown'])->toBeFalse()
        ->and(array_unique(array_column([$idle, ...$shown, $after], 'content')))->toHaveCount(1) // The page starts always in the same place
        ->and(array_unique(array_column([$idle, ...$shown, $after], 'height')))->toBe([6]);

    $page->assertNoJavaScriptErrors();
});
