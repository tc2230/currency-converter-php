<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis as Redis;

class CurrencyExchangeService extends Controller
{
    protected $currency_data;
    
    // constructor
    public function __construct(array $input_data) {
        $this->currency_data = $input_data; 
    }

    // amount validation function
    public function validate_amount(string $amount) {
        // Validate input amount with or without separator
        if (strpos($amount, ',') === false) {
            $pattern = "/^([1-9]\d*|0)(\.\d+)?$/";
        } else {
            $pattern = "/^(([1-9]\d{0,2})(,\d{3})*|0)(\.\d+)?$/";
        }
        return (bool) preg_match($pattern, $amount);
    }

    // conversion function
    public function convert($source, $target, $amount) {
        // get exchange rate
        $rate = $this->currency_data["currencies"][$source][$target];
        // convert amount string to float
        $amount = str_replace(',', '', $amount);
        // rounding
        $converted_value = round($amount*$rate, 2, PHP_ROUND_HALF_UP);
        // formmating
        $formatted_value = number_format($converted_value, 2, '.', ',');

        return $formatted_value;
    }
}
