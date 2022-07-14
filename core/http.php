<?php

namespace core;

use core\auth\header;

class http
{

  public static function onboarding($data)
  {
    $accepted = [
      'sendAdminRegToken' => true,
      'saveAdminPassword' => true,
      'adminLogin' => true,
      'adminToken' => true,
      'customerLogin' => true,
      'customerToken' => true,
      "sendForgotPasswordEmail" => true,
      "sendForgotPasswordEmailAdmin" => true,
      "verifyForgotPasswordLink" => true,
      "verifyForgotPasswordLinkAdmin" => true,
      "resetForgotPassword" => true,
      "resetForgotPasswordAdmin" => true,
      'adminLogin' => true,
      "getSingleBlog" => true,
      "getBlogList" => true,
      'regFormOne' => true,
      'regFormTwo' => true,
      'resendRegCode' => true,
      'regFormThree' => true,
    ];

    if (isset($accepted[$data->request->type])) {
      return true;
    } else {
      header::type('methodError');
      exit(json_encode(['error' => true, 'errorMessage' => 'Invalid request type']));
    }
  }
}
