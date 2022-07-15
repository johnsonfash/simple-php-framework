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
    $user = $model->getUser($variables->id, $columns);

    return $res->get_res([graph::data => $user]);
  }

  public static function getAddress($parent, $columns, $variables, $middleware_data)
  {
    $res = utils::build_res();
    $model = new ModelTest();
    $address = $model->getAddress($variables->user_id, $columns);

    return $res->get_res([graph::data => $address]);
  }

  public static function getGeoData($parent, $columns, $variables, $middleware_data)
  {
    $res = utils::build_res();
    $model = new ModelTest();
    $geo_data = $model->getGeoData($variables->address_id, $columns);

    return $res->get_res([graph::data => $geo_data]);
  }

  public static function getBio($parent, $columns, $variables, $middleware_data)
  {
    $res = utils::build_res();
    $model = new ModelTest();
    $user_bio = $model->getBio($variables->user_id, $columns);

    return $res->get_res([graph::data => $user_bio]);
  }

  public static function getTimeline($parent, $columns, $variables, $middleware_data)
  {
    $res = utils::build_res();
    $model = new ModelTest();
    $user_timeline = $model->getTimeline($variables->user_id, $columns);

    return $res->get_res([graph::data => $user_timeline]);
  }

  public static function getHistory($parent, $columns, $variables, $middleware_data)
  {
    $res = utils::build_res();
    $model = new ModelTest();
    $history = $model->getHistory($variables->user_id, $columns);

    return $res->get_res([graph::data => $history]);
  }
}
