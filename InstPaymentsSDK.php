<?php

class InstPaymentsSDK {

    private $host;
    private $apiKey;
    private $apiSecret;
    private $apiPassphrase;

    public function __construct($host, $apiKey, $apiSecret, $passphrase) {
        $this->host = $host;
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->apiPassphrase = $passphrase;
    }

    public function api_v1_orders($params) {
        $timestamp = $this->getMillisecond();
        $method = 'GET';
        $requestPath = '/api/v1/orders';
        $url = $this->host . $requestPath;

        $sign = $this->sign($timestamp, $method, $requestPath, http_build_query($params), '');
        $authorization = 'Inst:' . $this->apiKey . ':' . $timestamp . ':' . $sign;
        return $this->send_get($url, $params, $authorization);
    }

    public function api_v1_payment($post_data) {
        $timestamp = $this->getMillisecond();
        $method = 'POST';
        $requestPath = '/api/v1/payment';
        $url = $this->host . $requestPath;

        $sign = $this->sign($timestamp, $method, $requestPath, '', $post_data);
        $authorization = 'Inst:' . $this->apiKey . ':' . $timestamp . ':' . $sign;
        return $this->send_post($url, json_encode($post_data), $authorization);
    }

    private function sign($timestamp, $method, $requestPath, $queryString, $body) {
        $preHash = $this->preHash($timestamp, $method, $requestPath, $queryString, $this->apiKey, $body);
        $sign = hash_hmac('sha256', utf8_encode($preHash) , utf8_encode($this->apiSecret), true);
        return base64_encode($sign);
    }

    private function preHash($timestamp, $method, $requestPath, $queryString, $apiKey, $body) {
        $preHash = $timestamp . $method . $apiKey . $requestPath;
        if (!empty($queryString)) {
            $preHash = $preHash . '?' . urldecode($queryString);
        }

        $postStr = '';
        if (!empty($body)){
            foreach ($body as $key => $value) {
                if (is_array($value)) {
                    $postStr .= $key.'=' .json_encode($value, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE).'&';
                } else {
                    $postStr .= $key.'=' .$value.'&';
                }
            }
            $postStr = substr($postStr ,0, -1);
        }
        return $preHash . $postStr;
    }

    private function send_get( $url , $params , $authorization) {
        $url = $url . '?' . http_build_query($params);
        $curl = curl_init($url);
        curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true);
        $headers = array(
            "Content-Type: application/json;charset=utf-8",
            "Accept: application/json, text/plain, */*",
            "Authorization:" . $authorization,
            "Access-Passphrase:" . $this->apiPassphrase,
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $responseText = curl_exec($curl);
        if (!$responseText) {
            echo('INSTPAY NOTIFY CURL_ERROR: ' . var_export(curl_error($curl), true));
        }
        curl_close($curl);

        return $responseText;
    }

    private function send_post( $url , $post_data , $authorization) {

        $curl = curl_init($url);

        curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt ($curl, CURLOPT_POST, true);
        curl_setopt ($curl, CURLOPT_POSTFIELDS, ($post_data) );
        curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true);
        $headers = array(
            "Content-Type: application/json; charset=utf-8",
            "Accept: application/json",
            "Authorization:" . $authorization,
            "Access-Passphrase:" . $this->apiPassphrase,
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $responseText = curl_exec($curl);
        if (!$responseText) {
            echo('INSTPAY NOTIFY CURL_ERROR: ' . var_export(curl_error($curl), true));
        }
        curl_close($curl);

        return $responseText;
    }


    private function getMillisecond() {
        list($s1,$s2)=explode(' ',microtime());
        return (float)sprintf('%.0f',(floatval($s1)+floatval($s2))*1000);
    }

    public function formatArray($array) {
        if (is_array($array)) {
            ksort($array);
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $array[$key] = $this->formatArray($value);
                }
            }
        }
        return $array;
    }
}
