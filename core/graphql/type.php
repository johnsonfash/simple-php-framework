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

  public static function upload($id)
  {
    header::type();
    return json_encode(self::uploadType($id));
  }

  public static function uploadType($id)
  {
    $accepted = [
      'customerProfileImage' => true,
      'adminProfileImage' => true,
      'shippingInvoice' => true
    ];

    if (
      !isset($_FILES[graph::IMAGE_FILES]) ||
      !isset($_POST[graph::IMAGE_TYPE]) ||
      !isset($accepted[$_POST[graph::IMAGE_TYPE]])
    ) {
      return json_encode([graph::error => true, graph::errorMessage => 'Invalid image file or type']);
    }

    return  call_user_func([controlhandler::class, $_POST[graph::IMAGE_TYPE]],  $id, $_FILES[graph::IMAGE_FILES]);
  }


  public static function start($data, $middleware_data = null)
  {
    header::type();
    return json_encode(self::run(@$data->request, @$data->variables, $middleware_data));
  }

  public static function run($request, $variables = [], $middleware_data = null)
  {
    if (!$request || !isset($request->type) || !isset($request->return)) {
      return ['error' => true, 'errorMessage' => 'request, request.type and request.return cannot be empty or null'];
    }
    if (count((array)$request) !== 2) {
      return ['error' => true, 'errorMessage' => 'request object must contain only request.type & request.return'];
    }
    $type = self::getConstant('static', $request->type);
    if ($type['error']) {
      return $type;
    } else {
      if (isset($type['data']['input'])) {
        $inp_handler = self::inputCheck($type['data']['input'], $variables);
        if ($inp_handler['error']) {
          return $inp_handler;
        }
      }
      $retn_handler = self::returnCheck($type['data']['return'], $request->return);
      if ($retn_handler['error']) {
        return $retn_handler;
      }
      return controlhandler::dispatch($request, $variables, $type['data'], $middleware_data);
    }
  }

  public static function returnCheck2($backendReturn, $frontendReturn)
  {
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
          return self::returnCheck2($backend[graph::return], $frontendReturn->$key);
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


  public static function inputCheck2($backendInput, $frontendInput)
  {
    try {
      foreach ($frontendInput as $key => $value) {
        is_object($value) ? self::$stackTrace .= "[$key]--" : self::$stackTrace .= "$key--";
        if (!isset($backendInput[$key])) throw new Exception("return key '$key' is not present in the avaliable return type");
        if (is_object($frontendInput->$key)) {
          if (!is_array($backendInput[$key])) throw new Exception("return key '$key' of type '$backendInput[$key]' is not an object");
          $backend = self::getConstant('static', $backendInput[$key][graph::type]);
          if ($backend['error']) throw new Exception("no function call for $key");
          $backend = $backend[graph::data];
          return self::returnCheck2($backend[graph::return], $frontendInput->$key);
        } else {
          if (!is_string($value)) throw new Exception("$key is not a $backendInput[$key]");
          if (!in_array($value, graph::types)) throw new Exception("'$key' of type '$value' is incorrect");
          if ($backendInput[$key][0] != $value[0]) throw new Exception("'$key' of type '$value' is incorrect");
        }
      }
      return [graph::error => false];
    } catch (\Throwable $e) {
      return [graph::error => true, graph::errorMessage => $e->getMessage(), graph::trace => self::$stackTrace];
    }
  }

  public static function returnCheck($return, $requestReturn)
  {
    $response =  ["error" => false, "errorMessage" => null];
    if (isset($return[0])) {
      $return = $return[0];
    }
    if (is_object($requestReturn)) {
      foreach ($requestReturn as $key => $value) {
        if (in_array(strtoupper($key), graph::abortKeys)) {
          return ['error' => true, "errorMessage" => "input key '$key' is a reserved keyword"];
        }
        if (!in_array($key, array_keys($return))) {
          return ['error' => true, 'errorMessage' => "request.return.$key is not present in the available return object of request.type"];
        }
        if (is_object($value)) {
          if (is_array($return[$key])) {
            if (in_array(graph::query, array_keys($return[$key]))) {
              $resp = self::getConstant('static', $return[$key][graph::query]);
              if ($resp['error']) {
                return $resp;
              } else {
                $resp_return_type = $resp['data']['return'];
                //todo
                return self::returnCheck($resp_return_type, $value);
              }
              if (in_array(graph::map_input, $return[$key]) && count($return[$key][graph::map_input]) > 1) {
                return ['error' => true, 'errorMessage' => 'only one map key allowed by core graphql'];
              }
            } else {
              return ['error' => true, 'errorMessage' => "server error! origin: graphql request type!"];
            }
          } else {
            return ['error' => true, 'errorMessage' => "required object return for '$key' but found " . gettype($return[$key])];
          }
        } else {
          if (!in_array(strtolower($value), graph::types)) {
            return ['error' => true, 'errorMessage' => "graph.return.$key of type $return[$key] does not match request.return.$key of type $value"];
          }
          if (strtolower($value)[0] !== strtolower($return[$key][0])) {
            return ['error' => true, 'errorMessage' => "graph.return.$key of type $return[$key] does not match request.return.$key of type $value"];
          }
        }
      }
    } else {
      if (is_string($requestReturn)) {
        if ((strtolower($requestReturn) === 'b' || strtolower($requestReturn) === 'boolean') &&
          ($return === 'b' || $return === 'boolean')
        ) {
        } else {
          return ['error' => true, 'errorMessage' => "request return type of '" . gettype($requestReturn) . "' is invalid"];
        }
      } else {
        return ['error' => true, 'errorMessage' => "request return type of '" . gettype($requestReturn) . "' is invalid"];
      }
    }
    return $response;
  }

  public static function inputCheck($input, $variables)
  {
    $count = 0;
    $response =  ["error" => false, "errorMessage" => null];
    $diff = array_diff(array_keys((array) $variables),  array_keys($input));
    if ($diff) {
      return ['error' => true, 'errorMessage' => "variables." . utils::first($diff) . " is not available as an input key for the request type"];
    }
    foreach ($input as $key => $value) {
      if (in_array(strtoupper($key), graph::abortKeys)) {
        return ['error' => true, "errorMessage" => "input key '$key' is a reserved keyword"];
      }
      if (is_array($value)) {
        if (!isset($variables->$key) && strpos($value[0], '!') !== false) {
          return ['error' => true, "errorMessage" => "required varible key $key was not included"];
        } else if (isset($variables->$key)) {
          if (!is_array($variables->$key)) {
            return ['error' => true, "errorMessage" => "required varibles key '$key' in variables.$key needs to be an array"];
          }
          foreach ($variables->$key as $v) {
            if (gettype($v)[0] !== $value[0][0]) {
              return ["error" => true, "errorMessage" => "type $value[0] was required for variable key but received " . gettype($v)];
            }
          }
        }
      } else if (is_object($value)) {
        return self::inputCheck($value, $variables->$key);
      } else {
        if (strpos($value, '!') !== false) {
          $typeof = explode('!', strtolower($value));
          $typeof = trim($typeof[0]);
          if (in_array($typeof, graph::types)) {
            if (!isset($variables->$key)) {
              return ['error' => true, "errorMessage" => "required varible key '$key' of type '$typeof' was not included"];
            }
            $v_type = gettype($variables->$key);
            if ($v_type[0] !== $typeof[0]) {
              return ["error" => true, "errorMessage" => "type definition input.$key of '$typeof' and variables.$key of $v_type did not match"];
            }
          } else {
            return ["error" => true, "errorMessage" => "type '$typeof' defined in input query is invalid"];
          }
        } else {
          if (isset($variables->$key) && (gettype($variables->$key)[0] !== strtolower($value)[0])) {
            return ["error" => true, "errorMessage" => "variables." . array_search($variables->$key, (array)$variables) . " value of type '" . gettype($variables->$key) . "' is invalid"];
          }
        }
      }
      $count++;
    }
    return $response;
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

  public static function view($view = true)
  {
    header('content-type: application/json');
    self::$graphArray = self::getTypes();
    foreach (self::$graphArray as $k_1 => $v_1) {
      foreach ($v_1[graph::return] as $k_2 => $v_2) {
        if (is_array($v_2)) {
          self::$graphArray[$k_1][graph::return][$k_2] = self::$graphArray[$v_2[graph::type]][graph::return];
          foreach (self::$graphArray[$k_1][graph::return][$k_2] as $k_3 => $v_3) {
            if (is_array($v_3)) {
              self::$graphArray[$k_1][graph::return][$k_2][$k_3] = self::$graphArray[$v_3[graph::type]][graph::return];
              foreach (self::$graphArray[$k_1][graph::return][$k_2][$k_3] as $k_4 => $v_4) {
                if (is_array($v_4)) {
                  self::$graphArray[$k_1][graph::return][$k_2][$k_3][$k_4] = self::$graphArray[$v_4[graph::type]][graph::return];
                  foreach (self::$graphArray[$k_1][graph::return][$k_2][$k_3][$k_4] as $k_5 => $v_5) {
                    if (is_array($v_5)) {
                      self::$graphArray[$k_1][graph::return][$k_2][$k_3][$k_4][$k_5] = self::$graphArray[$v_5[graph::type]][graph::return];
                    }
                  }
                }
              }
            }
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
