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

    // conversion function (live exchange rate)
    public function convert_live($source, $target, $amount) {
        // get current exchange rate
        $rate = $this->get_rate($source, $target, $amount);

        // update redis data
        $this->update_redis_hash_data("currency-rate:$source", $target, $rate);

        // convert amount string to float
        $amount = str_replace(',', '', $amount);
        // rounding
        $converted_value = round($amount*$rate, 2, PHP_ROUND_HALF_UP);
        // formmating
        $formatted_value = number_format($converted_value, 2, '.', ',');

        return $formatted_value;
    }

    // conversion function (live exchange rate)
    private function update_redis_hash_data($key, $field, $value) {
        // update redis data
        Redis::hset($key, $field, $value);
    }

    // get live exchange rate
    private function get_rate($source, $target, $amount) {
        $url = "https://currency-conversion-and-exchange-rates.p.rapidapi.com/convert?from=$source&to=$target&amount=1";
        $headers = [
            'X-RapidAPI-Host' => 'currency-conversion-and-exchange-rates.p.rapidapi.com',
            'X-RapidAPI-Key' => 'cee8e22a92mshf1ee8869713d676p19d5d5jsn37285e4dac64',
        ];
        
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_TIMEOUT => 30,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "X-RapidAPI-Host: currency-conversion-and-exchange-rates.p.rapidapi.com",
                "X-RapidAPI-Key: cee8e22a92mshf1ee8869713d676p19d5d5jsn37285e4dac64"
            ],
        ]);
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        $data = json_decode($response, true);
        
        return $data["info"]["rate"];
    }
}
