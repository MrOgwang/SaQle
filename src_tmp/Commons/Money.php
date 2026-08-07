<?php

namespace SaQle\Commons;

final class Money {
     /**
     * Number of fraction digits for common currencies.
     * Unknown currencies default to 2.
     */
     private const FRACTION_DIGITS = [
         'JPY' => 0,
         'KRW' => 0,
         'VND' => 0,

         'BHD' => 3,
         'IQD' => 3,
         'JOD' => 3,
         'KWD' => 3,
         'LYD' => 3,
         'OMR' => 3,
         'TND' => 3,

         // Defaults to 2:
         'USD' => 2,
         'EUR' => 2,
         'GBP' => 2,
         'KES' => 2,
         'ZAR' => 2,
         'NGN' => 2,
         'CAD' => 2,
         'AUD' => 2,
     ];

     /**
     * Format a numeric amount.
     */
     public static function format(
         float|int $amount,
         ?string $currency = null,
         bool $with_symbol = true,
         bool $parentheses_for_negative = false,
         string $thousands_separator = ',',
         string $decimal_separator = '.'
     ) : string {

         $currency = $currency ? strtoupper($currency) : null;

         $decimals = self::FRACTION_DIGITS[$currency] ?? 2;

         $formatted = number_format(
             abs($amount),
             $decimals,
             $decimal_separator,
             $thousands_separator
         );

         if($with_symbol && $currency){
             $formatted = "{$currency} {$formatted}";
         }

         if($amount < 0){
             return $parentheses_for_negative ? "({$formatted})" : "-{$formatted}";
         }

         return $formatted;
     }
}