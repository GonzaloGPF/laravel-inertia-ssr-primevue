<?php

use App\Models\User;

/**
 * The fonts the page has loaded, and the origins its font files were asked to.
 *
 * @return array{fonts: list<string>, fileOrigins: list<string>, origin: string, icon: bool}
 */
function pageFonts($page): array
{
    return json_decode((string) $page->script(<<<'JS'
        document.fonts.ready.then(() => JSON.stringify({
            fonts: [...document.fonts].filter(font => font.status === 'loaded').map(font => font.family.replace(/"/g, '')),
            fileOrigins: [...new Set(performance.getEntriesByType('resource')
                .filter(entry => /\.(woff2?|ttf)$/.test(new URL(entry.name).pathname))
                .map(entry => new URL(entry.name).origin))],
            origin: location.origin,
            icon: document.fonts.check('24px "Material Symbols Outlined"'),
        }))
    JS), true);
}

test('the fonts are served by the application, not by a third party', function () {
    $this->actingAs(User::factory()->create(['language' => 'en']));

    $page = visit('/');

    $page->assertSee('This is the root page');

    $state = pageFonts($page);

    expect($state['fonts'])->toContain('Figtree')
        ->toContain('Material Symbols Outlined')
        ->and($state['icon'])->toBeTrue()
        ->and($state['fileOrigins'])->toBe([$state['origin']]);
});
