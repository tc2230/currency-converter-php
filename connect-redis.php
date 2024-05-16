<?php

$redis = new Redis();
//Connecting to Redis
$redis->connect('127.0.0.1', 6379);
// $redis->auth('password');

if ($redis->ping()) {
    echo "Redis Connected.\n";

    $array = ["TWD", "JPY", "USD"];
    foreach($array as $cur) {
        echo "From $cur: \n";
        $data = $redis->hgetall("currency-rate:$cur");
        foreach($data as $key=>$value) {
            echo "\t $key : $value";
            echo "\n";
        }
    }

    
}

?>