<?php

use App\Enums\Languages;
use App\Enums\Roles;
use App\Models\User;

/**
 * The cards of statistics, in the order of the page: its title and value (null while it is loading) and where it is.
 *
 * @return list<array{title: string, value: ?string, top: int, left: int, width: int}>
 */
function statCards($page): array
{
    return json_decode((string) $page->script(<<<'JS'
        JSON.stringify([...document.querySelectorAll('[data-stat-card]')].map(card => {
            const box = card.getBoundingClientRect()

            return {
                title: card.querySelector('.p-card-title span:not(.material-symbols-outlined)').innerText.trim(),
                value: card.querySelector('p')?.innerText.trim() ?? null,
                top: Math.round(box.top),
                left: Math.round(box.left),
                width: Math.round(box.width),
            }
        }))
    JS), true);
}

/**
 * What the chart is painted with: the pixels it draws on, and the color of its line (the most used one that is not a gray).
 *
 * @return array{painted: int, line: string}
 */
function chartPaint($page): array
{
    return json_decode((string) $page->script(<<<'JS'
        (() => {
            const canvas = document.querySelector('.p-chart canvas')
            if (!canvas) return JSON.stringify({ painted: 0, line: '' })

            const pixels = canvas.getContext('2d').getImageData(0, 0, canvas.width, canvas.height).data
            const colors = {}
            let painted = 0

            for (let i = 0; i < pixels.length; i += 4) {
                if (pixels[i + 3] > 0) painted++
                const [red, green, blue] = [pixels[i], pixels[i + 1], pixels[i + 2]]
                if (pixels[i + 3] === 255 && Math.max(red, green, blue) - Math.min(red, green, blue) > 60) {
                    colors[`${red},${green},${blue}`] = (colors[`${red},${green},${blue}`] ?? 0) + 1
                }
            }

            const line = Object.entries(colors).sort((a, b) => b[1] - a[1])[0]?.[0] ?? ''

            return JSON.stringify({ painted, line })
        })()
    JS), true);
}

/**
 * The users of each month, as the chart tells them to a screen reader, from the oldest month to the current one.
 *
 * @return list<int>
 */
function chartTotals($page): array
{
    $description = (string) $page->script("document.querySelector('.p-chart canvas')?.getAttribute('aria-label') ?? ''");

    preg_match_all('/: (\d+)/', $description, $totals);

    return array_map('intval', $totals[1]);
}

/**
 * Waits for the statistics to be loaded: every card with its value and the chart painted.
 */
function expectDashboardLoaded($page): void
{
    $loaded = false;

    for ($attempt = 0; $attempt < 80 && ! $loaded; $attempt++) {
        $cards = statCards($page);
        $loaded = count($cards) === 4
            && count(array_filter($cards, fn (array $card) => $card['value'] !== null)) === 4
            && chartPaint($page)['painted'] > 0;

        if (! $loaded) {
            usleep(100_000);
        }
    }

    expect($loaded)->toBeTrue();
}

beforeEach(function () {
    $this->admin = User::factory()->admin()->create([
        'name' => 'Ada Admin',
        'language' => Languages::EN,
        'created_at' => now(),
    ]);

    $this->actingAs($this->admin);
});

test('the dashboard has a card with the total of users and three template cards', function () {
    User::factory(4)->create();

    $page = visit(route('dashboard.show', absolute: false));

    expectDashboardLoaded($page);

    $cards = statCards($page);

    $page->assertSee('Total users')
        ->assertNoJavaScriptErrors();

    expect(array_column($cards, 'title'))->toBe(['Total users', 'Label 1', 'Label 2', 'Label 3'])
        ->and($cards[0]['value'])->toBe('5') // The admin and the 4 others
        ->and(array_slice(array_column($cards, 'value'), 1))->each->toMatch('/^\d{1,3}$/'); // The template ones are a random number
});

test('the chart gives the users created in each month of the last year', function () {
    $month = now()->startOfMonth();

    User::factory(2)->create(['created_at' => $month->copy()->subMonths(2)->addDays(10)]);
    User::factory()->create(['created_at' => $month->copy()->subMonths(5)->addDays(3)]);
    User::factory()->create(['created_at' => $month->copy()->subMonths(13)]); // Too old for the chart
    User::factory()->create(['created_at' => $month->copy()->subMonths(2)])->delete(); // Deleted

    $page = visit(route('dashboard.show', absolute: false));

    expectDashboardLoaded($page);

    expect(chartTotals($page))->toBe([0, 0, 0, 0, 0, 0, 1, 0, 0, 2, 0, 1]) // Until the current month, with the admin
        ->and(chartPaint($page)['painted'])->toBeGreaterThan(1000);

    $page->assertSee('New users per month')
        ->assertSee('Last 12 months')
        ->assertNoJavaScriptErrors();
});

test('the cards are in a row in a large screen and one below the other in a small one', function () {
    $page = visit(route('dashboard.show', absolute: false))->resize(1280, 900);

    expectDashboardLoaded($page);

    $cards = statCards($page);

    expect(array_unique(array_column($cards, 'top')))->toHaveCount(1) // The 4 in the same row
        ->and(array_unique(array_column($cards, 'left')))->toHaveCount(4);

    $page->resize(400, 900);
    usleep(300_000);

    $cards = statCards($page);

    expect(array_unique(array_column($cards, 'left')))->toHaveCount(1) // All of them in the same column
        ->and(array_column($cards, 'top'))->toBe(collect(array_column($cards, 'top'))->sort()->values()->all())
        ->and(array_unique(array_column($cards, 'top')))->toHaveCount(4)
        ->and(array_unique(array_column($cards, 'width')))->toHaveCount(1);
});

test('the cards and the chart are a pulsing skeleton until the statistics arrive', function () {
    $page = visit('/about');

    // Holds back the request of the deferred props, so the skeletons can be seen
    $page->script(<<<'JS'
        (() => {
            const setRequestHeader = XMLHttpRequest.prototype.setRequestHeader
            const send = XMLHttpRequest.prototype.send

            XMLHttpRequest.prototype.setRequestHeader = function (name, value) {
                if (name.toLowerCase() === 'x-inertia-partial-data') this.__deferred = true

                return setRequestHeader.call(this, name, value)
            }
            XMLHttpRequest.prototype.send = function (...args) {
                return this.__deferred ? setTimeout(() => send.apply(this, args), 2500) : send.apply(this, args)
            }
        })()
    JS);

    $page->click('Ada Admin')
        ->click('li[aria-label="Dashboard"]')
        ->assertRoute('dashboard.show');

    // The page is there before its statistics: the cards are shown, loading
    for ($attempt = 0; $attempt < 30 && count(statCards($page)) < 4; $attempt++) {
        usleep(100_000);
    }

    $skeletons = fn () => (int) $page->script("document.querySelectorAll('.p-skeleton').length");

    expect($skeletons())->toBe(5) // The 4 cards and the chart
        ->and(array_column(statCards($page), 'value'))->each->toBeNull()
        ->and(chartPaint($page)['painted'])->toBe(0);

    expectDashboardLoaded($page);

    expect($skeletons())->toBe(0);

    $page->assertNoJavaScriptErrors();
});

test('the chart is painted with the colors of the dark mode when the system uses it', function () {
    $light = visit(route('dashboard.show', absolute: false));
    expectDashboardLoaded($light);

    $dark = visit(route('dashboard.show', absolute: false))->inDarkMode();
    expectDashboardLoaded($dark);

    [$lightLine, $darkLine] = [chartPaint($light)['line'], chartPaint($dark)['line']];

    expect($lightLine)->not->toBe('')
        ->and($darkLine)->not->toBe('')
        ->and($darkLine)->not->toBe($lightLine); // The primary color is a lighter one in the dark mode
});

test('the dashboard is translated', function () {
    $this->admin->update(['language' => Languages::ES]);

    $page = visit(route('dashboard.show', absolute: false));

    expectDashboardLoaded($page);

    expect(array_column(statCards($page), 'title'))->toBe(['Usuarios totales', 'Etiqueta 1', 'Etiqueta 2', 'Etiqueta 3']);

    $page->assertSee('Nuevos usuarios por mes')
        ->assertSee('Últimos 12 meses')
        ->assertNoJavaScriptErrors();
});

test('a regular user that lands on the dashboard gets no statistics', function () {
    $this->actingAs(User::factory()->create(['role' => Roles::USER, 'language' => Languages::EN]));

    $page = visit(route('dashboard.show', absolute: false));

    $page->assertSee('This is the dashboard')
        ->assertDontSee('Total users')
        ->assertNoJavaScriptErrors();

    expect(statCards($page))->toBeEmpty()
        ->and(chartPaint($page)['painted'])->toBe(0);
});
