<?php

namespace controllers;

use core\utils;
use enum\graph;
use model\test as ModelTest;

trait test
{
  public static function getUser($parent, $columns, $variables, $middleware_data)
  {
    $res = utils::build_res();
    $model = new ModelTest();
    $value = $model->getUser($variables->id);

    if ($value[graph::error]) {
      return $res->get_res($value);
    }

    return $res->get_res([graph::data => true]);
  }

  public static function getAddress($parent, $columns, $variables, $middleware_data)
  {
    return [graph::data => ['id' => 1]];
  }

  public static function getGeoData($parent, $columns, $variables, $middleware_data)
  {
    return [graph::data => ['id' => 1]];
  }

  public static function getBio($parent, $columns, $variables, $middleware_data)
  {
    return [graph::data => ['id' => 1]];
  }

  public static function getTimeline($parent, $columns, $variables, $middleware_data)
  {
    return [graph::data => ['id' => 1]];
  }

  public static function getHistory($parent, $columns, $variables, $middleware_data)
  {
    return [graph::data => ['id' => 1]];
  }
}
