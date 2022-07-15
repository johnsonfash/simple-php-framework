<?php

namespace core\graphql;

use core\utils;
use enum\graph;
use handler\typehandler;

/**
 * control handles all queries in batches
 * dispatch only one inner matrix method for now
 */
class control
{
  private static $batch = [];

  /**
   * dispatch query to its respective controllers
   *
   * @param  object $query  e.g { type: "getUser", return: { name: "s", age: "i" } }
   * @param  object $variables e.g { id: 13 }
   * @param  array $type backendType e.g [ "input" => [ "id" : "integer!" ], "return" => [ "id" => "integer", "name" => "string", "age" => "integer"] ]
   * @param  mixed $middleware_data interger | object i.e 12 | { "id": 12, "role": "admin"} 
   * @return void
   */
  public static function mainThread($query, $variables, $type, $middleware_data)
  {
    try {
      self::dispatch($query, [], $variables, $type, $middleware_data, $query->type);
      $new_array = [];
      $data = self::combiner($query->return, self::$batch, $new_array, $query->type);
      return [graph::error => false, graph::errorMessage => null, graph::data => $data];
    } catch (\Throwable $e) {
      return [graph::error => true, graph::errorMessage => $e->getMessage()];
    }
  }

  /**
   * dispatch - dispatch query to its respective controllers
   *
   * @param  object $query  e.g { type: "getUser", return: { name: "s", age: "i" } }
   * @param  object $variables e.g { id: 13 }
   * @param  array $parent e.g [ "id": 13 ] | [[ "id": 12 ]] | [ ]
   * @param  array $type backendType e.g [ "input" => [ "id" : "integer!" ], "return" => [ "id" => "integer", "name" => "string", "age" => "integer"] ]
   * @param  mixed $middleware_data interger | object i.e 12 | { "id": 12, "role": "admin"} 
   * @param  string $__key nexted query identifier e.g __address => attached like i.e getAddress__address
   * @return void
   */
  public static function dispatch($query, $parent, $variables, $type, $middleware_data, $__key = '')
  {
    $columns = [];
    $batch = [];
    $include_column = [];
    if (is_object($query->return) || is_array($query->return)) {
      if (isset($query->meta)) {
        $query->return = $query->meta;
      }
      foreach ($query->return as $key => $value) {
        if (is_object($value) || is_array($value)) {
          $nexted = $type[graph::return][$key];
          $type = typehandler::getConstant('static', $nexted[graph::type])[graph::data];
          $explode =  explode('.', utils::first($nexted[graph::input]))[1];
          $include_column[] = $explode;
          $batch[] = [
            'query' => (object) ['type' => $nexted[graph::type], 'meta' => $value, 'return' => $type[graph::return]],
            'variable' => array_keys($nexted[graph::input])[0],
            'find' => $explode,
            'key' => $key,
            'type' => $type,
          ];
        } else {
          $columns[] = $key;
        }
      }
    }

    $main = self::handler($query->type, $parent, array_unique(array_merge($columns, $include_column)), $variables, $middleware_data)[graph::data];
    self::$batch[$__key] = $main;
    // self::$batch[$query->type . '__' . $__key] = $main;

    if ($main) {
      foreach ($batch as $_value) {
        if (isset($main[0])) {
          $count  = count($main);
          $value = [];
          for ($i = 0; $i < $count; $i++) {
            $value[] = $main[$i][$_value['find']];
          }
        } else {
          $value = $main[$_value['find']];
        }
        self::dispatch($_value['query'], $main, (object) [$_value['variable'] => $value], $_value['type'], $middleware_data, $_value['key']);
      }
    }
  }

  /**
   * handler - handles calling a function to a query, along with its meta data
   *
   * @param string $controller controller method to hadle the query
   * @param array $keys model keys to compare in query
   * @param object $variables controller variables input(s) for query
   * @return mixed array | boolean | int | string
   */
  public static function handler($controller, $parent, $columns, $variables, $middleware_data)
  {
    return  call_user_func([static::class, $controller], $parent, $columns, $variables, $middleware_data);
  }

  /**
   * comniner - handles combining queries together
   *
   * @param mixed $frontendReturn object | boolean | int | string
   * @param array $batched_array query values in array
   * @param array $new_array combined nexted + main query structure
   * @param string $starting_key main query type to start with
   * @return mixed
   */
  public static function combiner($frontendReturn, $batched_array, &$new_array, $starting_key)
  {
    if (is_object($frontendReturn)) {
      foreach ($frontendReturn as $key => $value) {
        if (is_object($value)) {
          $val = self::combiner($value, $batched_array, $new_array[$key], $key);
          $new_array[$key] = $val;
        } else {
          $new_array[$key] = @$batched_array[$starting_key][$key];
        }
      }
      return $new_array;
    } else {
      return $batched_array[$starting_key];
      switch ($frontendReturn[0]) {
        case 'b':
          return (bool) @$batched_array[$starting_key];
        case 'i':
          return (int) @$batched_array[$starting_key];
        case 'd':
          return (float) @$batched_array[$starting_key];
        case 'f':
          return (float) @$batched_array[$starting_key];
        case 's':
          return @trim(json_encode($batched_array[$starting_key]), "\"..'");
        default:
          return @$batched_array[$starting_key];
      }
    }
  }
}
