<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Trade;
use Faker\Generator as Faker;

$factory->define(Trade::class, function (Faker $faker) {
    $currencies = collect(['cad', 'ngn']);
    $fromCurrency = $currencies->random();
    $toCurrency = $currencies->reject(function ($currency) use ($fromCurrency) {
        return $currency == $fromCurrency;
    })->random();

    return [
        'amount' => $faker->numberBetween(100, 500000),
        'from_currency' => $fromCurrency,
        'to_currency' => $toCurrency,
        'rate'  => $faker->numberBetween(50, 500)
    ];
});
