<?php

use App\Models\User;

it('let auto complete users', function () {
    User::factory(3)->create();
    User::factory()->create([
        'name' => 'Test',
    ]);

    $data = $this->getJson(route('autocomplete.index', [
        'model' => 'user',
        'name' => 'Test'
    ]))->assertSuccessful()
        ->json('data');

    expect($data)->toHaveCount(1);
});
