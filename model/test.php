<?php

namespace model;

use core\mail;
use core\utils;
use database\db;
use enum\graph;

class test
{
  protected static $table;
  protected static $connection;

  function checkEmail($email)
  {
    if ($email) {
      return self::$connection->query()->table(self::$table, ['email'])->where('email', $email)->first();
    } else {
      return [graph::error => true];
    }
  }

  function getUser($id, $columns)
  {
    return self::$connection->query()->table(self::$table, $columns)->where('id', $id)->first();
  }

  function getAddress($id, $columns)
  {
    return self::$connection->query()->table("address", $columns)->where('user_id', $id)->first();
  }

  function getGeoData($id, $columns)
  {
    return self::$connection->query()->table("geo_data", $columns)->where('address_id', $id)->first();
  }

  function getBio($id, $columns)
  {
    return self::$connection->query()->table("user_bio", $columns)->where('user_id', $id)->first();
  }

  function getTimeline($id, $columns)
  {
    return self::$connection->query()->table("activity_timeline", $columns)->where('user_id', $id)->first();
  }

  function getHistory($id, $columns)
  {
    return self::$connection->query()->table("user_history", $columns)->where('user_id', $id)->first();
  }

  function __construct()
  {
    $this->connect();
  }

  function connect($table = 'users')
  {
    self::$table = $table;
    self::$connection = db::connect();
  }
}
