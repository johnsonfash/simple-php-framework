<?php

namespace core\graphql;

use core\auth\header;
use core\utils;
use enum\graph;
use Exception;
use handler\controlhandler;

class type
{

  public static $graphArray = [];
  public static $stackTrace = '';
  public static $nexted = 0;
  public static $build = [];

  public static function upload($id)
  {
    header::type();
    return json_encode(self::uploadType($id));
  }

  public static function uploadType($middleware_data)
  {
    $accepted = [
      'testImageUpload' => true,
    ];

    if (
      !isset($_FILES[graph::FILES]) ||
      !isset($_POST[graph::FILE_QUERY]) ||
      !isset($accepted[$_POST[graph::FILE_QUERY]])
    ) {
      return json_encode([graph::error => true, graph::errorMessage => 'Invalid image file or type']);
    }

    return  call_user_func([controlhandler::class, $_POST[graph::FILE_QUERY]], $_FILES[graph::FILES], $middleware_data);
  }


  public static function start($data, $middleware_data = null)
  {
    header::type();
    return json_encode(self::run(@$data->query, @$data->variables, $middleware_data));
  }

  public static function run($query, $variables = [], $middleware_data = null)
  {
    if (!isset($query->type) || !isset($query->return)) {
      return [graph::error => true, graph::errorMessage => 'query, query.type and query.return cannot be empty or null'];
    }
    if (count((array)$query) !== 2) {
      return [graph::error => true, graph::errorMessage => 'query object must contain only query.type & query.return'];
    }
    $type = self::getConstant('static', $query->type);
    if ($type[graph::error]) {
      return $type;
    } else {
      if (isset($type['data']['input'])) {
        $inp_handler = self::inputCheck($type['data']['input'], $variables);
        if ($inp_handler[graph::error]) {
          return $inp_handler;
        }
      }
      $retn_handler = self::returnCheck($type['data']['return'], $query->return);
      if ($retn_handler[graph::error]) {
        return $retn_handler;
      }
      return controlhandler::mainThread($query, $variables, $type['data'], $middleware_data);
    }
  }

  public static function returnCheck($backendReturn, $frontendReturn)
  {
    self::$nexted++;
    if (self::$nexted > graph::maxNextedQuery) throw new Exception("cannot exceed 5 nexted queries");

    try {
      if (is_string($backendReturn)) {
        if (
          $frontendReturn && (!in_array($frontendReturn, graph::types) || !is_string($frontendReturn) ||  ($backendReturn[0] != $frontendReturn[0]))
        ) {
          throw new Exception("$frontendReturn is not allowed as a return type for this query");
        }
        return [graph::error => false];
      }
      foreach ($frontendReturn as $key => $value) {
        is_object($value) ? self::$stackTrace .= "[$key]--" : self::$stackTrace .= "$key--";
        if (!isset($backendReturn[$key])) throw new Exception("return key '$key' is not present in the avaliable return type");
        if (is_object($frontendReturn->$key)) {
          if (!is_array($backendReturn[$key])) throw new Exception("return key '$key' of type '$backendReturn[$key]' is not an object");
          $backend = self::getConstant('static', $backendReturn[$key][graph::type]);
          if ($backend['error']) throw new Exception("no function call for $key");
          $backend = $backend[graph::data];
          return self::returnCheck($backend[graph::return], $frontendReturn->$key);
        } else {
          if (!is_string($value)) throw new Exception("$key is not a $backendReturn[$key]");
          if (!in_array($value, graph::types)) throw new Exception("'$key' of type '$value' is incorrect");
          if ($backendReturn[$key][0] != $value[0]) throw new Exception("'$key' of type '$value' is incorrect");
        }
      }
      return [graph::error => false];
    } catch (\Throwable $e) {
      return [graph::error => true, graph::errorMessage => $e->getMessage(), graph::trace => self::$stackTrace];
    }
  }

  public static function inputCheck($backendInput, $frontendInput)
  {
    try {
      $diff = array_diff(array_keys((array) $frontendInput),  array_keys($backendInput));
      if ($diff) throw new Exception("variables." . utils::first($diff) . " is not available as an input key for the request type");
      foreach ($backendInput as $key => $value) {
        if (is_string($value)) {
          if (strpos($value, '!') !== false) {
            if (!isset($frontendInput) || empty(@$frontendInput->$key))  throw new Exception("input key '$key' must be PROVIDED and NOT NULL");
            if (!in_array(gettype($frontendInput->$key), graph::types) || gettype($frontendInput->$key)[0] != $value[0]) throw new Exception("input '$key' of type " . gettype($frontendInput->$key) . " is not allowed");
          } else if (isset($frontendInput->$key)) {
            if (gettype($frontendInput->$key)[0] != $value[0]) throw new Exception("input '$key' of type " . gettype($frontendInput->$key->$key) . " is not allowed");
          }
        } else if (is_array($value)) {
          if (strpos($value[0], '!') !== false) {
            if (!isset($frontendInput->$key)) throw new Exception("input key '$key' is NOT SET");
            if (gettype(@$frontendInput->$key) != 'array') throw new Exception("input key '$key' is NOT ARRAY");
            if (empty(@$frontendInput->$key)) throw new Exception("input key '$key' is EMPTY");
          }
          if (isset($frontendInput->$key)) {
            foreach ($frontendInput->$key as $_val) {
              if (is_object($_val) && empty((array) $_val) && strpos($value[0], '!') !== false) throw new Exception("input key '$key' cannot have an empty object in its array");
              if (gettype($_val)[0] != $value[0][0])  throw new Exception("input key '$key' value of type " . gettype($_val) . " is not allowed");
            }
          }
        } else {
          throw new Exception("input '$key' of type " . gettype($frontendInput->$key) . " is not allowed");
        }
      }
      return [graph::error => false];
    } catch (\Throwable $e) {
      return [graph::error => true, graph::errorMessage => $e->getMessage(), graph::trace => self::$stackTrace];
    }
  }

  public static function getTypes()
  {
    $reflectClass = new \ReflectionClass(get_called_class());
    return $reflectClass->getConstants();
  }

  public static function viewEval($ar, $str)
  {
    $r = eval("return $str;");
    foreach ($ar[graph::return] as $key => $value) {
      if (is_array($value)) {
        eval($str . '[graph::return][' . $key . '] = self::$graphArray[' . $value . '[graph::type]][graph::return]');
        self::viewEval($value, $r . '[' . $key . ']');
      }
    }
  }

  public static function attach($key, $value)
  {
    if (is_array($value)) {
      self::$build[] = $key;
      $str1 = "['" . self::$build[0] . "']";
      $str2 = '';
      $count = count(self::$build);
      if ($count > graph::maxNextedQuery) throw new Exception('nexted query for ' . self::$build[0] . ' exceeds the maximum allowed');
      for ($i = 1; $i < $count; $i++) {
        $str2 .= "['" . self::$build[$i] . "']";
      }
      eval('self::$graphArray' . $str1 . '["return"]' . $str2 . ' = self::$graphArray[$value["type"]]["return"];');
      $current = eval('return self::$graphArray' . $str1 . '["return"]' . $str2 . ';');
      foreach ($current as $k => $v) {
        if (is_array($v)) {
          return self::attach($k, $v);
        }
      }
    }
  }

  public static function view($view = true)
  {
    header('content-type: application/json');
    self::$graphArray = self::getTypes();
    foreach (self::$graphArray as $k_1 => $v_1) {
      self::$build = [$k_1];
      foreach ($v_1[graph::return] as $k_2 => $v_2) {
        if (is_array($v_2)) {
          try {
            self::attach($k_2, $v_2);
          } catch (\Throwable $e) {
            return json_encode([graph::error => true, graph::errorMessage => $e->getMessage()]);
          }
        }
      }
    }
    ksort(self::$graphArray);
    if ($view) {
      return json_encode([
        "description" => [
          "maximum" => "this library only allows the maximum of two matrix query for now",
          "requirement_1" => "nexted queries should include type & optional input",
          "note_1" => 'please make sure to include & handle all type of input for both main queries and nexted queries',
          "note_2" => 'you can choose to include a description & name key on your API service type if you want',
          "warning_1" => 'auth fields like password and token must be managed internally and not exposed to the GRAPH VIEW return keys',
          graph::type => "used to specify the controller for a nexted query",
          graph::input => "used map input from the main query to the input of the nexted query e.g [next_query.input => main_query.input]",
        ],
        "types" => self::$graphArray
      ]);
    } else {
      $endpoint = self::getEndpoints();
      sort($endpoint);
      return json_encode($endpoint);
    }
  }

  public static function getEndpoints()
  {
    return array_keys(self::getTypes());
  }

  public static function getConstant(string $class, string $constant): array
  {
    try {
      return ['error' => false, "errorMessage" => null, "data" => constant("$class::$constant")];
    } catch (\Throwable $e) {
      return ['error' => true, "errorMessage" => "could not find a function call or reference to $constant"];
    } catch (\Exception $e) {
      return ['error' => true, "errorMessage" => "could not find a function call or reference to $constant"];
    }
  }
}
