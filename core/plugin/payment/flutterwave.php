<?php

namespace core\plugin\payment;

use core\axios;
use core\os;
use enum\graph;

class flutterwave
{
  public static function verify_payment($transaction_id)
  {
    $flutterKey = graph::flutterKey;
    $url_build = "https://api.flutterwave.com/v3/transactions/$transaction_id/verify";
    $resp = axios::get($url_build, [
      "Content-type" => "application/json",
      "Authorization" => "Bearer $flutterKey"
    ]);
    if ($resp['error']) {
      return $resp;
    } else {
      $resp = $resp['data'];
      if ($resp['status'] !== 'success' || $resp["data"]['processor_response'] !== 'Approved') {
        return ['error' => true, 'errorMessage' => "Unable to verify your payment for now. Please contact our support team."];
      } else {
        $amount = (int) $resp['data']['amount'];
        return  ['error' => false, 'data' => $amount];
      }
    }
  }
}
