<?php
//    echo "<pre>";
    header('Content-Type: application/json');
    require 'InstPaymentsSDK.php';
    $host = "https://api.sandbox.inst.money";
    $apiKey = "d7df99xxxxxxxxxxxxxxxxxxxxc405ca";
    $apiSecret = "6f34931f-xxxx-xxxx-xxxx-aa60c8d02c14";
    $apiPassphrase = "123123123";
    $sdk = new InstPaymentsSDK($host, $apiKey, $apiSecret, $apiPassphrase);

    $post_data = array(
        'currency' => 'USD',
        'amount' => '0.10',
        'cust_order_id' => 'php_test_' . time(),
    );
    $post_data = $sdk->formatArray($post_data);

    echo 'payment result:' . "\n";
    $result = $sdk->api_v1_payment($post_data);
    echo json_encode(json_decode($result), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n" . "\n";

    echo 'checkout result:' . "\n";
    $result = json_decode($result, true);
    $params = array(
        'order_id' => $result['result']['order_id'],
//        'order_id' => 'p22081704043211905',
    );
    $result_checkout = $sdk->api_v1_orders($params);
    echo json_encode(json_decode($result_checkout), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n";

