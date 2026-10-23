<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('the profile edit page loads without javascript errors', function () {
    $user = User::factory()->create(['language' => 'en']);

    $this->actingAs($user);

    $page = visit('/profile');

    $page->assertSee('Profile Information')
        ->assertNoSmoke();
});

test('a user can update their name and email and sees a visible confirmation', function () {
    $user = User::factory()->create(['language' => 'en']);

    $this->actingAs($user);

    $page = visit('/profile');

    $page->clear('input[name="name"]')
        ->type('input[name="name"]', 'Updated Name')
        ->click('form:has(input[name="name"]) button[type="submit"]')
        ->assertSee('User Updated')
        ->assertNoJavaScriptErrors();

    expect($user->fresh()->name)->toBe('Updated Name');
});

test('a user can change their language preference, it persists, and the UI retranslates immediately', function () {
    $user = User::factory()->create(['language' => 'en']);

    $this->actingAs($user);

    $page = visit('/profile');

    $page->assertSee('Language')
        ->click('span[name="language"]')
        ->click('li[aria-label="Spanish"]')
        ->click('form:has(input[name="name"]) button[type="submit"]')
        ->assertSee('Idioma')
        ->assertDontSee('Language')
        ->assertNoJavaScriptErrors();

    expect($user->fresh()->language->value)->toBe('es');

    $page->click('span[name="language"]')
        ->click('li[aria-label="Inglés"]')
        ->click('form:has(input[name="name"]) button[type="submit"]')
        ->assertSee('Language')
        ->assertNoJavaScriptErrors();

    expect($user->fresh()->language->value)->toBe('en');
});

test('a user can change their currency preference and it persists', function () {
    $user = User::factory()->create(['language' => 'en', 'currency' => 'dollar']);

    $this->actingAs($user);

    $page = visit('/profile');

    $page->click('span[name="currency"]')
        ->click('li[aria-label="Euro"]')
        ->click('form:has(input[name="name"]) button[type="submit"]')
        ->assertNoJavaScriptErrors();

    expect($user->fresh()->currency->value)->toBe('euro');
});

test('the profile form shows a visible error for a duplicate email', function () {
    $existing = User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create(['language' => 'en']);

    $this->actingAs($user);

    $page = visit('/profile');

    $page->clear('input[name="email"]')
        ->type('input[name="email"]', 'taken@example.com')
        ->click('form:has(input[name="name"]) button[type="submit"]')
        ->assertSee('The email has already been taken.')
        ->assertNoJavaScriptErrors();

    expect($user->fresh()->email)->not->toBe('taken@example.com');
});

test('the profile form exposes stable name and autocomplete attributes', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $page = visit('/profile');

    $page->assertAttribute('input[name="name"]', 'autocomplete', 'name')
        ->assertAttribute('input[name="email"]', 'autocomplete', 'email');
});

test('the profile shows the avatar of the user, or their initials', function () {
    Storage::fake('testing');
    $user = User::factory()->create(['name' => 'Grace Hopper', 'language' => 'en']);
    $this->actingAs($user);

    $page = visit('/profile');

    $page->assertSeeIn('form:has(input[name="name"]) .p-avatar', 'GH')
        ->assertMissing('form:has(input[name="name"]) .p-avatar img')
        ->assertNoJavaScriptErrors();

    $avatar = $user->saveFile(UploadedFile::fake()->image('grace.png'), 'avatar');

    $page = visit('/profile');

    $page->assertPresent('form:has(input[name="name"]) .p-avatar img');

    expect($page->script("document.querySelector('form:has(input[name=\"name\"]) .p-avatar img').getAttribute('src')"))
        ->toBe($avatar->url);
});

test('the avatar chosen in the profile is sent as a post that is handled as a put', function () {
    Storage::fake('testing');
    $user = User::factory()->create(['language' => 'en']);
    $this->actingAs($user);

    $page = visit('/profile');

    $page->attach('input[name="avatar"]', imageToUpload('me.png'))
        ->assertPresent('form:has(input[name="name"]) .p-avatar img');

    recordRequests($page);

    $page->click('form:has(input[name="name"]) button[type="submit"]')
        ->assertNoJavaScriptErrors();

    $request = sentRequest($page);

    expect($request['method'])->toBe('POST')
        ->and($request['url'])->toEndWith('/profile')
        ->and($request['body']['_method'])->toBe('put')
        ->and($request['body']['avatar'])->toBe('file:me.png');
});

test('a user can remove their avatar from the profile', function () {
    Storage::fake('testing');
    $user = User::factory()->create(['language' => 'en']);
    $avatar = $user->saveFile(UploadedFile::fake()->image('me.png'), 'avatar');
    $this->actingAs($user);

    $page = visit('/profile');

    $page->assertPresent('form:has(input[name="name"]) .p-avatar img')
        ->click('form:has(input[name="name"]) button[aria-label="Remove"]')
        ->assertMissing('form:has(input[name="name"]) .p-avatar img')
        ->click('form:has(input[name="name"]) button[type="submit"]')
        ->assertSee('User Updated')
        ->assertNoJavaScriptErrors();

    expect($user->fresh()->avatar)->toBeNull();

    Storage::disk('testing')->assertMissing($avatar->path);
});

test('each section of the profile is in its own card', function () {
    $user = User::factory()->create(['language' => 'en']);
    $this->actingAs($user);

    $page = visit('/profile');

    $page->assertPresent('.p-card');

    $cards = json_decode($page->script("JSON.stringify([...document.querySelectorAll('.p-card')].map(card => ({ title: card.querySelector('.p-card-title').innerText.trim(), name: !!card.querySelector('input[name=\"name\"]'), currentPassword: !!card.querySelector('input[name=\"current_password\"]'), delete: !!card.querySelector('button[aria-label=\"Delete User\"]') })))"), true);

    expect($cards)->toHaveCount(3)
        ->and($cards[0])->toMatchArray(['name' => true, 'currentPassword' => false, 'delete' => false])
        ->and($cards[0]['title'])->toContain('Profile Information')
        ->and($cards[1])->toMatchArray(['name' => false, 'currentPassword' => true, 'delete' => false])
        ->and($cards[1]['title'])->toContain('Update Password')
        ->and($cards[2])->toMatchArray(['name' => false, 'currentPassword' => false, 'delete' => true]);

    $page->assertNoJavaScriptErrors();
});

test('the account deletion is in a danger zone, a card with a red outline', function () {
    $user = User::factory()->create(['language' => 'en']);
    $this->actingAs($user);

    $page = visit('/profile');

    $page->assertSee('Danger Zone')
        ->assertSee('Deleting your account is permanent and cannot be undone.');

    // The colors of the page are not rgb, so they go through a canvas to read their rgb
    $outlines = json_decode($page->script(<<<'JS'
        JSON.stringify([...document.querySelectorAll('.p-card')].map(card => {
            const style = getComputedStyle(card)
            const context = document.createElement('canvas').getContext('2d')
            context.fillStyle = style.borderTopColor
            context.fillRect(0, 0, 1, 1)
            const [red, green, blue] = context.getImageData(0, 0, 1, 1).data

            return { width: parseFloat(style.borderTopWidth), red, green, blue, danger: !!card.querySelector('button[aria-label="Delete User"]') }
        }))
    JS), true);

    [$profile, $password, $danger] = $outlines;

    expect($profile['width'])->toEqual(0)
        ->and($password['width'])->toEqual(0)
        ->and($danger['danger'])->toBeTrue()
        ->and($danger['width'])->toBeGreaterThan(0.0)
        ->and($danger['red'])->toBeGreaterThan(200)
        ->and($danger['green'])->toBeLessThan(100)
        ->and($danger['blue'])->toBeLessThan(100);
});

test('the danger zone is translated', function () {
    $user = User::factory()->create(['language' => 'es']);
    $this->actingAs($user);

    $page = visit('/profile');

    $page->assertSee('Zona Peligrosa')
        ->assertSee('Eliminar tu cuenta es permanente y no se puede deshacer.')
        ->assertNoJavaScriptErrors();
});
