<?php

namespace core\graphql;

use core\utils;
use enum\graph;

/**
 * control handles all queries in batches
 * dispatch only one inner matrix method for now
 */
class control
{

  private  static $main_batch = [];
  private  static $nexted_batch = [];

  /**
   * dispatch query to its respective controllers
   *
   * @param  object $request http request variable
   * @param  object $variables http variables, default = { }
   * @param  array $type backend type = (array) [ query, input, return ]
   * @return void 
   */
  public static function dispatch($request, $variables, $type, $middleware_data)
  {
    $main_query = [
      graph::controller => $request->type,
      graph::variables => $variables,
      graph::keys => []
    ];

    $nexted_queries = [];
    $main_key_lookout = [];


    //batch loop
    if ($request->return !== graph::boolean) {
      foreach ($request->return as $key => $value) {
        if (is_object($value)) {

          if (utils::arrayInArray($type[graph::return])) {
            $type[graph::return] = utils::first($type[graph::return]);
          }
          if (isset($type[graph::require][$key])) {
            if (strpos($type[graph::require][$key], '.') !== false) {
              $type[graph::require][$key] = utils::last(explode('.', $type[graph::require][$key]));
            }
            $main_query[graph::keys][] = $type[graph::require][$key];
          }
          $nexted_queries[$key][graph::controller] = $type[graph::return][$key][graph::query];
          $nexted_queries[$key][graph::keys] = array_keys((array) $value);
          if (isset($type[graph::return][$key][graph::map_input])) {
            foreach ($type[graph::return][$key][graph::map_input] as $_key => $_value) {
              $k = explode('.', $_key)[1];
              $v = explode('.', $_value)[1];
              $nexted_queries[$key][graph::variables][$k] = null;
              $nexted_queries[$key][graph::meta][] = $v;
              $main_key_lookout[$type[graph::require][$key]] = null;
            }
          } else {
            $nexted_queries[$key][graph::variables] = (object)[];
            $nexted_queries[$key][graph::meta] = [];
          }
          if (isset($type[graph::return][$key][graph::compare])) {
            $nexted_queries[$key][graph::keys][] = $type[graph::return][$key][graph::compare];
          }
        } else {
          $main_query[graph::keys][] = $key;
        }
      }
    }

    // execute first batch query

    self::$main_batch = self::handler(
      $main_query[graph::controller],
      array_unique($main_query[graph::keys]),
      $main_query[graph::variables],
      $middleware_data
    );

    //eheck if main_batch has error
    if (self::$main_batch['error']) {
      return self::$main_batch;
    } else {
      self::$main_batch = self::$main_batch['data'];
      if ($main_query[graph::controller] === 'customerLogin' && isset(self::$main_batch['id'])) {
        $middleware_data = self::$main_batch['id'];
      }
    }

    $main_key_lookout = self::variable_extractor($main_key_lookout, self::$main_batch);


    foreach ($nexted_queries as $__key => $__value) {
      foreach ($__value[graph::variables] as $__k => $__v) {
        $__value[graph::variables][$__k] = isset($main_key_lookout[$type[graph::require][$__key]]) ? $main_key_lookout[$type[graph::require][$__key]] : "";
      }
      $single_query = self::handler(
        $__value[graph::controller],
        array_unique($__value[graph::keys]),
        (object) $__value[graph::variables],
        $middleware_data
      );
      if ($single_query['error']) {
        return $single_query;
      } else {
        self::$nexted_batch[$__key] = $single_query['data'];
      }
    }

    return combiner::run(self::$main_batch, self::$nexted_batch, $request->return, $type);
  }

  /**
   * handler handles query
   *
   * @param string $controller controller method to hadle the query
   * @param array $keys model keys to compare in query
   * @param object $variables controller variables input(s) for query
   * @return array
   */
  public static function handler($controller, $keys, $variables, $middleware_data)
  {
    return  call_user_func([static::class, $controller], $keys, $variables, $middleware_data);
  }

  /**
   * variable_extractor extracts keys from database array values
   *
   * @param  array $variables = keys to extract to array
   * @param  array $data = array values from the database
   * @return array
   */

  public static function variable_extractor($variables, $main_batch)
  {
    $final = [];
    if (!is_bool($main_batch)) {
      if (utils::arrayInArray($main_batch)) {
        foreach ($main_batch as $value) {
          foreach ($variables as $k => $v) {
            $final[$k][] = $value[$k];
          }
        }
      } else {
        foreach ($variables as $_k => $_v) {
          $final[$_k] = $main_batch[$_k];
        }
      }
    }
    return $final;
  }
}
