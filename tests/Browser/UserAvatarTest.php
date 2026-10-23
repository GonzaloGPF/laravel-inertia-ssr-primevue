<?php

use App\Enums\Languages;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

const AVATAR_DIALOG = '[data-pc-name="dialog"]';

beforeEach(function () {
    Storage::fake('testing');

    $this->admin = User::factory()->admin()->create([
        'name' => 'Ada Admin',
        'email' => 'ada@example.com',
        'language' => Languages::EN,
    ]);

    // The newest user is listed first
    $this->grace = User::factory()->create(['name' => 'Grace Hopper', 'email' => 'grace@example.com']);

    $this->actingAs($this->admin);
});

test('the table shows the avatar of the users that have one and the initials of the rest', function () {
    $avatar = $this->grace->saveFile(UploadedFile::fake()->image('grace.png'), 'avatar');

    $page = visit(route('users.index', absolute: false));

    $page->assertSee('Grace Hopper')
        ->assertPresent('.p-datatable-tbody > tr:nth-child(1) .p-avatar img')
        ->assertSeeIn('.p-datatable-tbody > tr:nth-child(2) .p-avatar', 'AA')
        ->assertMissing('.p-datatable-tbody > tr:nth-child(2) .p-avatar img')
        ->assertNoJavaScriptErrors();

    expect($page->script("document.querySelector('.p-datatable-tbody > tr:nth-child(1) .p-avatar img').getAttribute('src')"))
        ->toBe($avatar->url);
});

test('the avatar chosen when creating a user is sent with the form', function () {
    $page = visit(route('users.index', absolute: false));

    $page->click('Create')
        ->assertMissing(AVATAR_DIALOG.' .p-avatar img')
        ->attach(AVATAR_DIALOG.' input[name="avatar"]', imageToUpload())
        ->assertPresent(AVATAR_DIALOG.' .p-avatar img')
        ->type(AVATAR_DIALOG.' input[name="name"]', 'Linus Torvalds')
        ->type(AVATAR_DIALOG.' input[name="email"]', 'linus@example.com');

    recordRequests($page);

    $page->click(AVATAR_DIALOG.' button[type="submit"]')
        ->assertNoJavaScriptErrors();

    $request = sentRequest($page);

    expect($request['method'])->toBe('POST')
        ->and($request['url'])->toEndWith('/admin/users')
        ->and($request['body']['avatar'])->toBe('file:avatar.png')
        ->and($request['body']['name'])->toBe('Linus Torvalds');
});

test('the edit modal shows the current avatar and sends the new one as a post', function () {
    $previous = $this->grace->saveFile(UploadedFile::fake()->image('old.png'), 'avatar');

    $page = visit(route('users.index', absolute: false));

    $page->assertSee('Grace Hopper')
        ->click(rowAction(1, 'Edit'))
        ->assertPresent(AVATAR_DIALOG.' .p-avatar img');

    expect($page->script("document.querySelector('".AVATAR_DIALOG." .p-avatar img').getAttribute('src')"))
        ->toBe($previous->url);

    $page->attach(AVATAR_DIALOG.' input[name="avatar"]', imageToUpload('new.png'));

    recordRequests($page);

    $page->click(AVATAR_DIALOG.' button[type="submit"]')
        ->assertNoJavaScriptErrors();

    $request = sentRequest($page);

    // A file cannot go in a PUT request, so it is a POST that Laravel handles as a PUT
    expect($request['method'])->toBe('POST')
        ->and($request['url'])->toEndWith('/admin/users/'.$this->grace->id)
        ->and($request['body']['_method'])->toBe('put')
        ->and($request['body']['avatar'])->toBe('file:new.png');
});

test('the user can remove the avatar of a user', function () {
    $avatar = $this->grace->saveFile(UploadedFile::fake()->image('grace.png'), 'avatar');

    $page = visit(route('users.index', absolute: false));

    $page->assertSee('Grace Hopper')
        ->click(rowAction(1, 'Edit'))
        ->assertPresent(AVATAR_DIALOG.' .p-avatar img')
        ->click(AVATAR_DIALOG.' button[aria-label="Remove"]')
        ->assertMissing(AVATAR_DIALOG.' .p-avatar img')
        ->assertMissing(AVATAR_DIALOG.' button[aria-label="Remove"]')
        ->click(AVATAR_DIALOG.' button[type="submit"]')
        ->assertDontSee('Edit User')
        ->assertNoJavaScriptErrors();

    expect($this->grace->fresh()->avatar)->toBeNull();

    Storage::disk('testing')->assertMissing($avatar->path);
});

test('discarding a chosen avatar goes back to the current one', function () {
    $this->grace->saveFile(UploadedFile::fake()->image('grace.png'), 'avatar');

    $page = visit(route('users.index', absolute: false));

    $page->assertSee('Grace Hopper')
        ->click(rowAction(1, 'Edit'));

    $current = $page->script("document.querySelector('".AVATAR_DIALOG." .p-avatar img').getAttribute('src')");

    $page->attach(AVATAR_DIALOG.' input[name="avatar"]', imageToUpload('new.png'));

    expect($page->script("document.querySelector('".AVATAR_DIALOG." .p-avatar img').getAttribute('src')"))
        ->toStartWith('blob:');

    $page->click(AVATAR_DIALOG.' button[aria-label="Remove"]');

    expect($page->script("document.querySelector('".AVATAR_DIALOG." .p-avatar img').getAttribute('src')"))
        ->toBe($current);

    $page->click(AVATAR_DIALOG.' button[type="submit"]')
        ->assertDontSee('Edit User');

    expect($this->grace->fresh()->avatar->name)->toBe('grace.png');
});
