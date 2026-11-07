<?php

use App\Enums\Roles;
use App\Models\User;
use App\Traits\IsFilterable;
use Illuminate\Database\Eloquent\Model;

it('let auto complete users', function () {
    $this->signIn();
    User::factory(3)->create();
    User::factory()->create([
        'name' => 'Test',
    ]);

    $data = $this->getJson(route('autocomplete.index', [
        'model' => 'user',
        'name' => 'Test',
    ]))->assertSuccessful()
        ->json('data');

    expect($data)->toHaveCount(1);
});

it('tells a guest that they are not logged, and leaks nothing of the users', function () {
    User::factory()->create(['name' => 'Secret Person', 'email' => 'secret@example.com']);

    $response = $this->getJson(route('autocomplete.index', ['model' => 'user']))
        ->assertUnauthorized();

    expect($response->getContent())->not->toContain('Secret Person')
        ->not->toContain('secret@example.com');
});

it('sends a guest that does not ask for JSON to log in', function () {
    $this->get(route('autocomplete.index', ['model' => 'user']))
        ->assertRedirect(route('login'));
});

it('does not find a model that does not exist, or that cannot be filtered', function (string $model) {
    $this->signIn();

    $this->getJson(route('autocomplete.index', ['model' => $model]))
        ->assertNotFound();
})->with([
    'not a model' => 'nothing',
    'a model that is not filterable' => 'file',
    'something that is not in the models' => 'Illuminate\Support\Str',
]);

it('names what it lists, as the other responses do', function () {
    $this->signIn();

    $message = $this->getJson(route('autocomplete.index', ['model' => 'user']))
        ->assertSuccessful()
        ->json('message');

    // It used to be the model itself, as JSON, where the name of the model goes
    expect($message)->not->toContain('{')
        ->not->toContain('models.')
        ->not->toContain('"');
});

it('is up to the policy of the model: without one, it is not for anybody', function () {
    $this->signIn();

    // A filterable model with no policy
    $model = new class extends Model
    {
        use IsFilterable;
    };
    class_alias($model::class, 'App\Models\Unpoliced');

    $this->getJson(route('autocomplete.index', ['model' => 'unpoliced']))
        ->assertForbidden();
});

it('forbids a regular user to list the users, as they are not for them', function () {
    User::factory()->create(['name' => 'Secret Person', 'email' => 'secret@example.com']);
    $this->signIn(User::factory()->create(['role' => Roles::USER]));

    $response = $this->getJson(route('autocomplete.index', ['model' => 'user']))
        ->assertForbidden();

    expect($response->getContent())->not->toContain('Secret Person')
        ->not->toContain('secret@example.com');
});
