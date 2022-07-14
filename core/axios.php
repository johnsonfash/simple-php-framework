<?php

namespace core;

use core\utils;

class axios
{

  public static function get(string $url, array $headers = [])
  {

    if (strpos(utils::first($headers), ':') === false) {
      $header = [];
      foreach ($headers as $key => $value) {
        $header[] = $key . ":" . $value;
      }
      $headers = $header;
    }
    
    $arr = [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 40,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => $headers,
    ];
    $arr[CURLOPT_CUSTOMREQUEST] = 'GET';
    $arr[CURLOPT_URL] = $url;
    $curl = curl_init();
    curl_setopt_array($curl, $arr);
    $result = curl_exec($curl);
    if (utils::is_json($result)) $result = json_decode($result, true);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
      return ['error' => true, 'errorMessage' => $err];
    } else {
      return ['error' => false, 'data' => $result];
    }
  }


  /**
   * post
   *
   * @param  string $url e.g https://example.com
   * @param  array $headers e.g ["Content-type: application/json", "Authorization: Bearer 123456789"]
   * @param  array $body e.g ["name" => "John Doe", "age" => 25]
   * @return array e.g ["error" => false, "data" => ["name" => "Ben", "age" => 30]]
   */
  public static function json(string $url, array $headers = [], array $body = [])
  {

    if (!is_array($body) || !is_array($headers)) {
      return ['error' => true, 'errorMessage' => 'Please provide a valid associative array'];
    }

    if (strpos(utils::first($headers), ':') === false) {
      $header = [];
      foreach ($headers as $key => $value) {
        $header[] = $key . ":" . $value;
      }
      $headers = $header;
    }

    $arr = [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_POST => true,
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 40,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => $headers,
    ];
    $arr[CURLOPT_POSTFIELDS] = json_encode($body);
    $arr[CURLOPT_URL] = $url;

    $curl = curl_init();
    curl_setopt_array($curl, $arr);
    $result = curl_exec($curl);
    if (utils::is_json($result)) $result = json_decode($result, true);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
      return ['error' => true, 'errorMessage' => $err];
    } else {
      return ['error' => false, 'data' => $result];
    }
  }


  /**
   * post
   *
   * @param  string $url e.g https://example.com
   * @param  array $headers e.g ["Content-type: application/json", "Authorization: Bearer 123456789"]
   * @param  array $body e.g ["name" => "John Doe", "age" => 25]
   * @return array e.g ["error" => false, "data" => ["name" => "Ben", "age" => 30]]
   */
  public static function post(string $url, array $headers = [], array $body = [])
  {

    if (!is_array($body) || !is_array($body)) {
      return ['error' => true, 'errorMessage' => 'Please provide a valid associative array'];
    }

    if (strpos(utils::first($headers), ':') === false) {
      $header = [];
      foreach ($headers as $key => $value) {
        $header[] = $key . ":" . $value;
      }
      $headers = $header;
    }


    $arr = [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_POST => true,
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 40,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => $headers,
    ];

    $arr[CURLOPT_POSTFIELDS] = http_build_query($body);
    $arr[CURLOPT_URL] = $url;

    $curl = curl_init();
    curl_setopt_array($curl, $arr);
    $result = curl_exec($curl);
    if (utils::is_json($result)) $result = json_decode($result, true);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
      return ['error' => true, 'errorMessage' => $err];
    } else {
      return ['error' => false, 'data' => $result];
    }
  }
}
