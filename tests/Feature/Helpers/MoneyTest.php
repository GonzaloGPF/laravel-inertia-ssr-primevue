<?php

use App\Enums\Currencies;
use App\Enums\Languages;
use Illuminate\Support\Number;

it('formats money in the currency and the language of the logged user', function (Languages $language, Currencies $currency, string $expected) {
    $this->signIn(attributes: ['language' => $language, 'currency' => $currency]);

    expect(money(1234.5))->toBe($expected);
})->with([
    'dollars in English' => [Languages::EN, Currencies::DOLLAR, '$1,234.50'],
    'euros in Spanish' => [Languages::ES, Currencies::EURO, "1.234,50\u{a0}€"],
]);

it('formats money in the language of the application when the user has none', function () {
    $this->signIn(attributes: ['language' => null, 'currency' => Currencies::DOLLAR]);

    expect(money(1234.5))->toBe(Number::currency(1234.5, 'USD', config('app.locale')));
});
