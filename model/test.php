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


  function test($variable)
  {
    // self::$connection->query()->table("customers")->where('id', $id)->update([
    //   "wallet -" => $amount
    // ]);
  }

  function __construct()
  {
    $this->connect();
  }

  function connect($table = 'shipping')
  {
    self::$table = $table;
    self::$connection = db::connect();
  }
}
