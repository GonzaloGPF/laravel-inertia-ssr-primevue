<?php

use App\Enums\Currencies;
use App\Enums\Languages;
use App\Enums\Roles;
use App\Models\User;

const DIALOG = '[data-pc-name="dialog"]';

beforeEach(function () {
    $this->admin = User::factory()->admin()->create([
        'name' => 'Ada Admin',
        'email' => 'ada@example.com',
        'language' => Languages::EN,
    ]);

    $this->actingAs($this->admin);
});

test('the create button opens a modal with all the user inputs', function () {
    $page = visit(route('users.index', absolute: false));

    $page->assertDontSee('Create User')
        ->click('Create')
        ->assertSee('Create User')
        ->assertPresent(DIALOG.' input[name="name"]')
        ->assertPresent(DIALOG.' input[name="email"]')
        ->assertPresent(DIALOG.' input[name="phone"]')
        ->assertPresent(DIALOG.' input[name="age"]')
        ->assertPresent(DIALOG.' [name="role"]')
        ->assertPresent(DIALOG.' [name="language"]')
        ->assertPresent(DIALOG.' [name="currency"]')
        ->assertNoJavaScriptErrors();
});

test('the modal can be cancelled without creating anything', function () {
    $page = visit(route('users.index', absolute: false));

    $page->click('Create')
        ->type(DIALOG.' input[name="name"]', 'Someone')
        ->click(DIALOG.' button[aria-label="Cancel"]')
        ->assertDontSee('Create User')
        ->assertNoJavaScriptErrors();

    expect(User::count())->toBe(1);
});

test('creating a user closes the modal and refreshes the list', function () {
    $page = visit(route('users.index', absolute: false));

    $page->click('Create')
        ->type(DIALOG.' input[name="name"]', 'Grace Hopper')
        ->type(DIALOG.' input[name="email"]', 'grace@example.com')
        ->type(DIALOG.' input[name="phone"]', '666666666')
        ->click(DIALOG.' button[type="submit"]')
        ->assertDontSee('Create User')
        ->assertSee('Grace Hopper')
        ->assertSee('grace@example.com')
        ->assertNoJavaScriptErrors();

    $user = User::firstWhere('email', 'grace@example.com');

    expect($user->name)->toBe('Grace Hopper')
        ->and($user->phone)->toBe('666666666')
        ->and($user->role)->toBe(Roles::USER)
        ->and($user->language)->toBe(Languages::ES)
        ->and($user->currency)->toBe(Currencies::EURO);
});

test('the selected role, language and currency are used to create the user', function () {
    $page = visit(route('users.index', absolute: false));

    $page->click('Create')
        ->type(DIALOG.' input[name="name"]', 'Linus Admin')
        ->type(DIALOG.' input[name="email"]', 'linus@example.com')
        ->click(DIALOG.' [name="role"]')
        ->click('li[aria-label="Admin"]')
        ->click(DIALOG.' [name="language"]')
        ->click('li[aria-label="English"]')
        ->click(DIALOG.' [name="currency"]')
        ->click('li[aria-label="Dollar"]')
        ->click(DIALOG.' button[type="submit"]')
        ->assertDontSee('Create User')
        ->assertSee('linus@example.com')
        ->assertNoJavaScriptErrors();

    $user = User::firstWhere('email', 'linus@example.com');

    expect($user->role)->toBe(Roles::ADMIN)
        ->and($user->language)->toBe(Languages::EN)
        ->and($user->currency)->toBe(Currencies::DOLLAR);
});

test('the required inputs keep the modal open when submitting it empty', function () {
    $page = visit(route('users.index', absolute: false));

    $page->click('Create')
        ->click(DIALOG.' button[type="submit"]')
        ->assertSee('Create User')
        ->assertNoJavaScriptErrors();

    expect(User::count())->toBe(1);
});

test('server validation errors are shown below the input and the modal stays open', function () {
    $page = visit(route('users.index', absolute: false));

    $page->click('Create')
        ->type(DIALOG.' input[name="name"]', 'Grace Hopper')
        ->type(DIALOG.' input[name="email"]', 'grace@example.com')
        ->type(DIALOG.' input[name="phone"]', str_repeat('6', 256))
        ->click(DIALOG.' button[type="submit"]')
        ->assertSee('Create User')
        ->assertSee('The phone may not be greater than 255 characters.')
        ->assertNoJavaScriptErrors();

    expect(User::count())->toBe(1);
});

test('an already registered email is reported keeping the typed values', function () {
    $page = visit(route('users.index', absolute: false));

    $page->click('Create')
        ->type(DIALOG.' input[name="name"]', 'Another Ada')
        ->type(DIALOG.' input[name="email"]', 'ada@example.com')
        ->click(DIALOG.' button[type="submit"]')
        ->assertSee('Create User')
        ->assertSee('The email has already been taken.')
        ->assertValue(DIALOG.' input[name="name"]', 'Another Ada')
        ->assertNoJavaScriptErrors();

    expect(User::count())->toBe(1);
});

test('the modal can be used again after a creation', function () {
    $page = visit(route('users.index', absolute: false));

    $page->click('Create')
        ->type(DIALOG.' input[name="name"]', 'First One')
        ->type(DIALOG.' input[name="email"]', 'first@example.com')
        ->click(DIALOG.' button[type="submit"]')
        ->assertSee('first@example.com')
        ->click('Create')
        ->assertSee('Create User')
        ->assertValue(DIALOG.' input[name="name"]', '')
        ->assertNoJavaScriptErrors();
});

test('the age is sent to create the user and shown in the list', function () {
    $page = visit(route('users.index', absolute: false));

    $page->click('Create')
        ->type(DIALOG.' input[name="name"]', 'Grace Hopper')
        ->type(DIALOG.' input[name="email"]', 'grace@example.com')
        ->type(DIALOG.' input[name="age"]', '85')
        ->click(DIALOG.' button[type="submit"]')
        ->assertDontSee('Create User')
        ->assertSee('Grace Hopper')
        ->assertNoJavaScriptErrors();

    expect(User::firstWhere('email', 'grace@example.com')->age)->toBe(85);

    $age = $page->script("[...document.querySelectorAll('.p-datatable-tbody > tr')].map(row => row.children[3].textContent.trim())[0]");

    expect($age)->toBe('85');
});

test('the age is optional to create a user', function () {
    $page = visit(route('users.index', absolute: false));

    $page->click('Create')
        ->type(DIALOG.' input[name="name"]', 'Grace Hopper')
        ->type(DIALOG.' input[name="email"]', 'grace@example.com')
        ->click(DIALOG.' button[type="submit"]')
        ->assertDontSee('Create User')
        ->assertSee('grace@example.com')
        ->assertNoJavaScriptErrors();

    expect(User::firstWhere('email', 'grace@example.com')->age)->toBeNull();
});

test('the number inputs have no spin buttons', function () {
    $page = visit(route('users.index', absolute: false));

    $page->click('Create')
        ->assertPresent(DIALOG.' input[name="age"]');

    $styles = $page->script("(() => { const input = document.querySelector('[data-pc-name=\"dialog\"] input[name=\"age\"]'); return JSON.stringify({ input: getComputedStyle(input).appearance, inner: getComputedStyle(input, '::-webkit-inner-spin-button').appearance, outer: getComputedStyle(input, '::-webkit-outer-spin-button').appearance }) })()");

    $styles = json_decode($styles, true);

    // `textfield` is what hides them in Firefox, and in Chrome and Safari they have no appearance
    expect($styles['input'])->toBe('textfield')
        ->and($styles['inner'])->toBeIn(['none', 'textfield'])
        ->and($styles['outer'])->toBeIn(['none', 'textfield']);
});
