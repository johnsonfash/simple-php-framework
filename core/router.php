<?php

namespace core;

class router
{
  private static $routes = array();
  private static $pathNotFound = null;
  private static $methodNotAllowed = null;

  public static function get(string $expression, callable $function, callable $middleware = null)
  {
    array_push(self::$routes, array(
      'expression' => $expression,
      'function' => $function,
      'method' => 'get',
      'middleware' => $middleware
    ));
  }


  public static function post(string $expression, callable $function, callable $middleware = null)
  {
    array_push(self::$routes, array(
      'expression' => $expression,
      'function' => $function,
      'method' => 'post',
      'middleware' => $middleware
    ));
  }

  public static function json(string $expression, callable $function, callable $middleware = null)
  {
    array_push(self::$routes, array(
      'expression' => $expression,
      'function' => $function,
      'method' => 'json',
      'middleware' => $middleware
    ));
  }

  public static function getAll(): array
  {
    return self::$routes;
  }

  public static function pathNotFound($function)
  {
    self::$pathNotFound = $function;
  }

  public static function methodNotAllowed($function)
  {
    self::$methodNotAllowed = $function;
  }

  public static function run($basepath = '', $case_matters = false, $trailing_slash_matters = false, $multimatch = false)
  {
    $basepath = rtrim($basepath, '/');
    if (isset($_SERVER['REQUEST_URI'])) $parsed_url = parse_url($_SERVER['REQUEST_URI']);
    $path = '/';



    if (isset($parsed_url['path'])) {
      // If the trailing slash matters
      if ($trailing_slash_matters) {
        $path = $parsed_url['path'];
      } else {
        // If the path is not equal to the base path (including a trailing slash)
        if ($basepath . '/' != $parsed_url['path']) {
          // Cut the trailing slash away because it does not matters
          $path = rtrim($parsed_url['path'], '/');
        } else {
          $path = $parsed_url['path'];
        }
      }
    }



    $path = urldecode($path);


    $method = $_SERVER['REQUEST_METHOD'];

    $path_match_found = false;

    $route_match_found = false;


    foreach (self::$routes as $route) {

      // If the method matches check the path

      // Add basepath to matching string
      if ($basepath != '' && $basepath != '/') {
        $route['expression'] = '(' . $basepath . ')' . $route['expression'];
      }

      // Add 'find string start' automatically
      $route['expression'] = '^' . $route['expression'];

      // Add 'find string end' automatically
      $route['expression'] = $route['expression'] . '$';

      // Check path match
      if (preg_match('#' . $route['expression'] . '#' . ($case_matters ? '' : 'i') . 'u', $path, $matches)) {
        $path_match_found = true;
        // Cast allowed method to array if it's not one already, then run through all methods
        foreach ((array)$route['method'] as $allowedMethod) {
          // Check method match
          if (strtolower($method) == strtolower($allowedMethod)) {
            array_shift($matches); // Always remove first element. This contains the whole string

            if ($basepath != '' && $basepath != '/') {
              array_shift($matches); // Remove basepath
            }

            $user = null;
            if ($route['middleware']) {
              $user = $route['middleware']();
              array_push($matches, $user);
            }
            if ($return_value = call_user_func_array($route['function'], $matches)) {
              echo (string) $return_value;
            }
            $route_match_found = true;
            // Do not check other routes
            break;
          }
          if (strtolower($method) == 'post' && strtolower($allowedMethod) == 'json') {

            $user = null;
            $json = json_decode(file_get_contents('php://input'));
            if ($route['middleware']) {
              $user = $route['middleware']($json);
            }
            if ($return_value = call_user_func($route['function'], $json, $user)) {
              header('content-type: application/json');
              echo (string) $return_value;
            }
            $route_match_found = true;
            break;
          }
        }
      }

      // Break the loop if the first found route is a match
      if ($route_match_found && !$multimatch) {
        break;
      }
    }

    // No matching route was found
    if (!$route_match_found) {
      // But a matching path exists
      if ($path_match_found) {
        if (self::$methodNotAllowed) {
          call_user_func_array(self::$methodNotAllowed, array($path, $method));
        }
      } else {
        if (self::$pathNotFound) {
          call_user_func_array(self::$pathNotFound, array($path));
        }
      }
    }
  }
}
