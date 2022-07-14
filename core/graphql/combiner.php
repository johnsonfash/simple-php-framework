<?php

namespace core\graphql;

use core\utils;
use enum\graph;

class combiner
{
  /**
   * handle structuring of data in request return format
   *
   * @param  array $main_batch array of main query = e.g [] | [[],[]]
   * @param  array $inner_batches assoc array values of inner queries = e.g ["key" => [[],[]]]
   * @param  object $requestReturn structure from the return body of the request = e.g ["key" => "s", "inner_key" => ["key" => "s"]]
   * @param  array $graph structure from the return body of the graph = e.g ["key" => "s", "inner_key" => ["graph_key" => "value"]]
   * @return void
   */
  public static function run($main_batch, $inner_batches, $requestReturn, $graph)
  {
    $final = [];
    if (is_bool($main_batch)) {
      $final =  $main_batch;
    } else {
      if (utils::arrayInArray($main_batch)) {
        $count = 0;
        foreach ($main_batch as $value) {
          foreach ($requestReturn as $k => $v) {
            if (is_object($v)) {
              $compare = [];
              if (isset($graph[graph::return][$k][graph::compare])) {
                $compare[] = $graph[graph::return][$k][graph::compare];
              }
              if (isset($graph[graph::require][$k])) {
                $require = $graph[graph::require][$k];
                if (strpos($require, '.')) {
                  $compare[] = utils::last(explode('.', $require));
                } else {
                  $compare[] = $require;
                }
              }
              if (is_bool($inner_batches[$k])) {
                $final[$count][$k] = $inner_batches[$k];
              } else {
                $final[$count][$k] = self::innerQueues($value, $inner_batches[$k], $compare, $requestReturn->$k);
              }
            } else {
              $final[$count][$k] = $value[$k];
            }
          }
          $count++;
        }
      } else {
        $main_arr = [];
        $final = [];
        if (is_bool($main_batch)) {
          $final =  $main_batch;
        } else {
          foreach ($main_batch as $key => $value) {
            if (isset($requestReturn->$key)) {
              $main_arr[$key] = $value;
            }
          }
          $arr = [];
          foreach ($inner_batches as $key => $value) {
            if (is_bool($value)) {
              $arr[$key] = $value;
            } else {
              $key_graph = (array) $requestReturn->$key;
              if (utils::arrayInArray($value)) {
                foreach ($value as $k => $v) {
                  foreach ($v as $__key => $__value) {
                    if (isset($key_graph[$__key])) {
                      $arr[$key][$k][$__key] = $__value;
                    }
                  }
                }
              } else {
                foreach ($value as $k => $v) {
                  if (isset($key_graph[$k])) {
                    $arr[$key][$k] = $v;
                  }
                }
              }
            }
          }
          $final =  array_merge($main_arr, $arr);
        }
      }
    }
    return ['error' => false, "errorMessage" => null, "data" => $final];
  }

  public static function innerQueues($main_data, $inner_data, $require, $graph)
  {
    $final = [];
    if (count($require) === 2) {
      $final = array_map(function ($item) use ($graph) {
        $arr = [];
        foreach ($graph as $key => $value) {
          $arr[$key] = $item[$key];
        }
        return $arr;
      }, array_filter($inner_data, function ($item) use ($main_data, $require) {
        if ($item[$require[0]] === $main_data[$require[1]]) {
          return true;
        } else {
          return false;
        }
      }));
    } else {
      $final = array_map(function ($item) use ($graph) {
        $arr = [];
        foreach ($graph as $key => $value) {
          $arr[$key] = $item[$key];
        }
        return $arr;
      }, $inner_data);
    }
    if (count($final) === 1) {
      $final = utils::first($final);
    }
    return $final;
  }
}
