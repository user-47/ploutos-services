<?php

namespace App\Managers;

use Brick\Math\RoundingMode;
use Brick\Money\CurrencyConverter;
use Brick\Money\ExchangeRateProvider\ConfigurableProvider;
use Brick\Money\Money;

class CurrencyManager
{
    /**
     * Transforms an amount to the currency lowest unit.
     * E.g USD 10.56 = 1056, JPY 1056 = 1056
     * @param float|int $amount
     */
    public static function toMinor($amount, string $currency): int
    {
        $money = Money::of($amount, strtoupper($currency));
        return $money->getMinorAmount()->toInt();
    }

    /**
     * Transforms an amount in currency lowest unit to currency default scale.
     * E.g 1056 = USD 10.56, 1056 = JPY 1056
     */
    public static function ofMinor(int $amount, string $currency): string
    {
        return (string) Money::ofMinor($amount, strtoupper($currency))->getAmount();
    }

    /**
     * Returns an amount in currency lowest unit to various formats
     * 
     * - amount  => amount in currency default scale without currency
     * - display => amount in currency default scale with currency
     * - minor_amount => amount in currency lowest unit of amount
     */
    public static function allFormats(int $amount, string $currency): array
    {
        $money = Money::ofMinor($amount, strtoupper($currency));
        return [
            'amount' => (string) $money->getAmount(),
            'currency' => $money->getCurrency()->getCurrencyCode(),
            'display' => $money->formatTo('en_US'),
            'minor_amount' => $money->getMinorAmount()->toInt(),
        ];
    }

    /**
     * @param float|int|string $amount
     * @param float|int $rate
     */
    public static function convertPrecise($amount, string $from, string $to, $rate)
    {
        $converted = self::convert($amount, $from, $to, $rate, false);
        return (string) $converted->getAmount();
    }

    /**
     * @param float|int|string $amount
     * @param float|int $rate
     */
    public static function convertMinor($amount, string $from, string $to, $rate)
    {
        $converted = self::convert($amount, $from, $to, $rate);
        return $converted->getMinorAmount()->toInt();
    }

    /**
     * @param float|int|string $amount
     * @param float|int|string $rate
     */
    private static function convert($amount, string $from, string $to, $rate, bool $isMinor = true)
    {
        $from = strtoupper($from);
        $to   = strtoupper($to);

        if ($from == $to) {
            return $amount;
        }

        $money = $isMinor ? Money::ofMinor($amount, $from) : Money::of($amount, $from);

        $provider = new ConfigurableProvider();
        $provider->setExchangeRate($from, $to, $rate);
        $converter = new CurrencyConverter($provider);
        return $converter->convert($money, $to, RoundingMode::UP);
    }
}