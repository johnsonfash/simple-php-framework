<?php

namespace controllers;

use core\utils;
use enum\graph;
use model\test as ModelTest;

trait test
{
  public static function getShippingFee($columns, $variables, $middleware_data)
  {
    $res = utils::build_res();
    $model = new ModelTest();
    $value = $model->test($variables);

    if ($value[graph::error]) {
      return $res->get_res($value);
    }

    return $res->get_res([graph::data => true]);
  }

  public static function getAdmin_getName()
  {
  }
}
