<?php

use App\Enums\Currencies;
use App\Enums\Languages;
use App\Enums\Roles;
use App\Models\User;

beforeEach(function () {
    create(User::class, [], 3); // Extra users
});

it('can filter users by role', function () {
    User::query()->update(['role' => Roles::USER]);

    create(User::class, ['role' => Roles::ADMIN]);

    $data = User::filter(['role' => [Roles::ADMIN->value]])->get();

    expect($data)->toHaveCount(1);
});

it('can filter users by verified', function () {
    User::query()->update(['email_verified_at' => null]);

    create(User::class, ['email_verified_at' => today()]);

    $data = User::filter(['verified' => true])->get();
    expect($data)->toHaveCount(1);

    $data = User::filter(['verified' => false])->get();
    expect($data)->toHaveCount(3);
});

it('can filter users by email', function () {
    create(User::class, ['email' => 'test@mail.com']);

    $data = User::filter(['email' => 'test'])->get();

    expect($data)->toHaveCount(1);
});

it('can filter users by phone', function () {
    create(User::class, ['phone' => '666666666']);

    $data = User::filter(['phone' => '666666'])->get();

    expect($data)->toHaveCount(1);
});

it('sorts users by id descending by default, whatever their creation date', function () {
    $oldest = User::query()->oldest('id')->first();
    $oldest->forceFill(['created_at' => now()->addYear()])->saveQuietly();

    $ids = User::filter([])->pluck('id')->all();

    expect($ids)->toBe(User::query()->orderByDesc('id')->pluck('id')->all());
});

it('sorts users by the given sort input instead of the default order', function () {
    $names = User::filter(['sort' => 'name|asc'])->pluck('name')->all();

    expect($names)->toBe(User::query()->orderBy('name')->pluck('name')->all());
});

it('falls back to the default order when the sort input is malformed', function () {
    $ids = User::filter(['sort' => 'name'])->pluck('id')->all();

    expect($ids)->toBe(User::query()->orderByDesc('id')->pluck('id')->all());
});

it('can filter users by name', function () {
    create(User::class, ['name' => 'Zelda Unique']);

    $data = User::filter(['name' => 'zelda'])->get();

    expect($data)->toHaveCount(1)
        ->and($data->first()->name)->toBe('Zelda Unique');
});

it('can filter users by several roles at once', function () {
    User::query()->update(['role' => Roles::USER]);

    create(User::class, ['role' => Roles::ADMIN]);

    expect(User::filter(['role' => [Roles::ADMIN->value, Roles::USER->value]])->get())->toHaveCount(4)
        ->and(User::filter(['role' => [Roles::ADMIN->value]])->get())->toHaveCount(1)
        ->and(User::filter(['role' => Roles::USER->value])->get())->toHaveCount(3);
});

it('can filter users by language and currency', function () {
    User::query()->update(['language' => Languages::ES, 'currency' => Currencies::EURO]);

    create(User::class, ['language' => Languages::EN, 'currency' => Currencies::DOLLAR]);

    expect(User::filter(['language' => Languages::EN->value])->get())->toHaveCount(1)
        ->and(User::filter(['currency' => Currencies::DOLLAR->value])->get())->toHaveCount(1)
        ->and(User::filter(['language' => Languages::ES->value])->get())->toHaveCount(3);
});

it('combines the filters of users', function () {
    create(User::class, ['name' => 'Zelda Admin', 'role' => Roles::ADMIN]);
    create(User::class, ['name' => 'Zelda User', 'role' => Roles::USER]);

    $data = User::filter(['name' => 'Zelda', 'role' => [Roles::ADMIN->value]])->get();

    expect($data)->toHaveCount(1)
        ->and($data->first()->name)->toBe('Zelda Admin');
});

it('can filter users by ranges of their creation date', function () {
    User::query()->update(['created_at' => now()]); // The factory creates them in any date of the last year

    $oldest = create(User::class, ['created_at' => now()->subYear()]);
    create(User::class, ['created_at' => now()]);

    expect(User::filter(['max_created_at' => now()->subMonths(6)->toDateString()])->get()->pluck('id')->all())->toBe([$oldest->id])
        ->and(User::filter(['min_created_at' => now()->subMonths(6)->toDateString()])->get())->toHaveCount(4);
});

describe('global search', function () {
    beforeEach(function () {
        $this->ada = create(User::class, ['name' => 'Zyxel Lovelace', 'email' => 'zyxel@analytical.org', 'phone' => '600111222']);
        $this->grace = create(User::class, ['name' => 'Grace Hopper', 'email' => 'grace@navy.mil', 'phone' => '600333444']);
    });

    it('searches in the name, the email and the phone', function (string $search, string $expected) {
        $data = User::filter(['search' => $search])->get();

        expect($data)->toHaveCount(1)
            ->and($data->first()->name)->toBe($expected);
    })->with([
        'by name' => ['lovelace', 'Zyxel Lovelace'],
        'by email' => ['navy.mil', 'Grace Hopper'],
        'by phone' => ['333444', 'Grace Hopper'],
        'by a part of a word' => ['ovela', 'Zyxel Lovelace'],
        'ignoring the case' => ['HOPPER', 'Grace Hopper'],
    ]);

    it('needs every word to be in some of the columns', function () {
        expect(User::filter(['search' => 'grace navy'])->get())->toHaveCount(1) // name and email
            ->and(User::filter(['search' => 'zyxel 600111'])->get())->toHaveCount(1) // name and phone
            ->and(User::filter(['search' => 'zyxel hopper'])->get())->toHaveCount(0);
    });

    it('ignores the spaces around and between the words', function () {
        expect(User::filter(['search' => "  zyxel \t  lovelace  "])->get())->toHaveCount(1);
    });

    it('does not filter with an empty search', function (mixed $search) {
        expect(User::filter(['search' => $search])->get())->toHaveCount(5); // 3 extra users and the 2 above
    })->with(['empty' => [''], 'spaces' => ['   ']]);

    it('searches the wildcards of a like as plain characters', function () {
        create(User::class, ['name' => '100% Real']);
        create(User::class, ['name' => 'Snake_case Person']);

        expect(User::filter(['search' => '%'])->get())->toHaveCount(1)
            ->and(User::filter(['search' => '100%'])->get())->toHaveCount(1)
            ->and(User::filter(['search' => 'e_c'])->get())->toHaveCount(1)
            ->and(User::filter(['search' => 'snake\\'])->get())->toHaveCount(0);
    });

    it('ignores a search that is not a text', function () {
        expect(User::filter(['search' => ['zyxel']])->get())->toHaveCount(5);
    });

    it('combines with the other filters', function () {
        $this->grace->update(['role' => Roles::ADMIN]);
        $this->ada->update(['role' => Roles::USER]);

        $data = User::filter(['search' => '600', 'role' => [Roles::ADMIN->value]])->get();

        expect($data)->toHaveCount(1)
            ->and($data->first()->name)->toBe('Grace Hopper');
    });

    it('does not find the deleted users', function () {
        $this->ada->delete();

        expect(User::filter(['search' => 'zyxel'])->get())->toHaveCount(0);
    });

    it('keeps the default order and the pagination', function () {
        create(User::class, ['name' => 'Zyxel Third']);

        $users = User::filter(['search' => 'zyxel'])->get();

        expect($users->pluck('name')->all())->toBe(['Zyxel Third', 'Zyxel Lovelace']);
    });
});

it('can filter users by a range of ages', function () {
    User::query()->update(['age' => 50]);

    create(User::class, ['age' => 18]);
    create(User::class, ['age' => 30]);
    create(User::class, ['age' => 65]);

    expect(User::filter(['min_age' => '30'])->get())->toHaveCount(5) // The 3 extra ones, 30 and 65
        ->and(User::filter(['max_age' => '30'])->get())->toHaveCount(2) // 18 and 30
        ->and(User::filter(['min_age' => '18', 'max_age' => '30'])->get()->pluck('age')->sort()->values()->all())->toBe([18, 30])
        ->and(User::filter(['min_age' => '31', 'max_age' => '49'])->get())->toHaveCount(0);
});

it('combines the age range with the global search and the rest of filters', function () {
    create(User::class, ['name' => 'Zyxel Young', 'age' => 20, 'role' => Roles::USER]);
    create(User::class, ['name' => 'Zyxel Old', 'age' => 80, 'role' => Roles::USER]);

    $data = User::filter(['search' => 'zyxel', 'min_age' => '50', 'role' => [Roles::USER->value]])->get();

    expect($data)->toHaveCount(1)
        ->and($data->first()->name)->toBe('Zyxel Old');
});

describe('sorting', function () {
    it('sorts users by an attribute in both directions', function (string $sort, array $expected) {
        User::query()->update(['age' => 99]);

        create(User::class, ['name' => 'B Person', 'age' => 30]);
        create(User::class, ['name' => 'A Person', 'age' => 50]);
        create(User::class, ['name' => 'C Person', 'age' => 20]);

        $names = User::filter(['sort' => $sort, 'name' => 'Person'])->pluck('name')->all();

        expect($names)->toBe($expected);
    })->with([
        'name ascending' => ['name|asc', ['A Person', 'B Person', 'C Person']],
        'name descending' => ['name|desc', ['C Person', 'B Person', 'A Person']],
        'age ascending' => ['age|asc', ['C Person', 'B Person', 'A Person']],
        'age descending' => ['age|desc', ['A Person', 'B Person', 'C Person']],
        'uppercase direction' => ['name|DESC', ['C Person', 'B Person', 'A Person']],
    ]);

    it('orders by id descending when the sorted values are repeated', function () {
        User::query()->update(['role' => Roles::USER]);

        $first = create(User::class, ['role' => Roles::ADMIN]);
        $second = create(User::class, ['role' => Roles::ADMIN]);

        $ids = User::filter(['sort' => 'role|asc'])->pluck('id')->all();

        expect(array_slice($ids, 0, 2))->toBe([$second->id, $first->id]);
    });

    it('can sort by id', function () {
        $ids = User::filter(['sort' => 'id|asc'])->pluck('id')->all();

        expect($ids)->toBe(User::query()->orderBy('id')->pluck('id')->all());
    });

    it('ignores a sort that is not valid', function (mixed $sort) {
        $ids = User::filter(['sort' => $sort])->pluck('id')->all();

        expect($ids)->toBe(User::query()->orderByDesc('id')->pluck('id')->all()); // The default order
    })->with([
        'unknown column' => ['nope|asc'],
        'unknown direction' => ['name|sideways'],
        'empty direction' => ['name|'],
        'empty attribute' => ['|asc'],
        'a column with an injection' => ['name; drop table users|asc'],
        'not a string' => [['name|asc']],
    ]);

    it('keeps the filters and the pagination when sorting', function () {
        create(User::class, ['name' => 'Zyxel B', 'age' => 40]);
        create(User::class, ['name' => 'Zyxel A', 'age' => 60]);

        $page = User::filter(['search' => 'zyxel', 'sort' => 'age|desc'])->paginate(1, ['*'], 'page', 1);

        expect($page->total())->toBe(2)
            ->and($page->items()[0]->name)->toBe('Zyxel A');
    });
});
