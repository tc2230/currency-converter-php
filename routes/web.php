<?php

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CurrencyExchangeService as CurrencyExchangeService;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/phpinfo', function () {
    phpinfo();
});

Route::get('/convert', function (Request $request) {
    // initialize service using sample data from local json file
    $default_path = '../exchange_rate.json';
    $json = file_get_contents($default_path); 
    $sample_data = json_decode($json, true); 
    $service = new CurrencyExchangeService($sample_data);

    // set rules for input validation
    $availiable_currency = array_keys($sample_data['currencies']);
    $rules = [
        'source' => 'required|in:'.implode(',',$availiable_currency),
        'target' => 'required|in:'.implode(',',$availiable_currency),
        'amount' => 'required',
    ];

    // validate input arguments
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json([
            'msg' => $validator->errors()->all(), 
            'amount' => null
        ]);
    }

    // retrieve args
    $source = $request->input('source');
    $target = $request->input('target');
    $amount = $request->input('amount');

    // validate input amount
    if (!$service->validate_amount($amount)) {
        return response()->json([
            'msg' => 'invalid amount', 
            'amount' => null
        ]);
    }

    // format response
    return response()->json([
        "msg" => "success", 
        "amount" => $service->convert($source, $target, $amount)
    ]);
});

Route::get('/convert_live', function (Request $request) {
    // initialize service with empty array
    $service = new CurrencyExchangeService([]);

    // set rules for input validation
    $rules = [
        'source' => 'required',
        'target' => 'required',
        'amount' => 'required',
    ];

    // validate input arguments
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json([
            'msg' => $validator->errors()->all(), 
            'amount' => null
        ]);
    }

    // retrieve args
    $source = $request->input('source');
    $target = $request->input('target');
    $amount = $request->input('amount');

    // validate input amount
    if (!$service->validate_amount($amount)) {
        return response()->json([
            'msg' => 'invalid amount', 
            'amount' => null
        ]);
    }

    // format response
    return response()->json([
        "msg" => "success", 
        "amount" => $service->convert_live($source, $target, $amount)
    ]);
});

Route::get('/convert_r', function (Request $request) {
    // initialize service with empty array
    $service = new CurrencyExchangeService([]);

    // retrieve args
    $source = $request->input('source');
    $target = $request->input('target');
    $amount = $request->input('amount');

    // format response
    return response()->json([
        "msg" => "success", 
        "amount" => $service->convert_r($source, $target, $amount)
    ]);
});
?>