<?php

namespace core\auth;

use core\auth\jwt\JWT;
use core\auth\jwt\Key;
use core\os;
use DateTimeImmutable;
use dev\benchmark;
use enum\graph;
use Exception;
use Throwable;

class header
{

  private static $bench;

  public static function benchmarkStart()
  {
    $app = new benchmark;
    $app->step('start');
    self::$bench = $app;
  }

  public static function benchmarkEnd($type = "json")
  {
    self::$bench->step('end');
    $report = self::$bench->getReport('start', 'end');
    if ($type === 'json') {
      return json_encode($report);
    } else {
      return $report;
    }
  }

  public static function increaseMemory($mb = 64)
  {
    ini_set('memory_limit', $mb . "M");
  }

  public static function increaseTime($seconds = 256)
  {
    set_time_limit($seconds);
  }

  public static function showError()
  {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
  }
  /**
   * auth handler for a user
   *
   * @param  string $for "customer" | "admin"
   * @return mixed user | error 
   */
  public static function auth($type = graph::access_token, $for = graph::customer)
  {
    $headers = getallheaders();
    if (isset($headers[graph::Authorization])) {
      $auth = $headers[graph::Authorization];
      if (strpos($auth, 'Bearer ') !== false && strlen($auth) > 12) {
        $secretKey = os::fromEnvFile(graph::encryptionKey);
        $token = trim(explode(' ', $auth)[1]);
        $host = "localhost"; // https://simple-php-framework.com
        try {
          $decode = JWT::decode($token, new Key($secretKey, 'HS512'));
          if ($decode->iss !== $host || !is_numeric($decode->user) || ($for == graph::admin && !isset($decode->role))) {
            self::type('unauthorized');
            exit(json_encode(["error" => true, "errorMessage" => "Unauthorized user"]));
          }
          if ($type === graph::refresh_token) {
            self::createToken($decode->user, $for, $type, isset($decode->role) ? $decode->role : null);
          }
          if ($for == graph::admin) {
            self::createToken($decode->user, graph::admin, graph::access_token, $decode->role);
            return (object) ["id" => $decode->user, "role" => $decode->role, "user_type" => $decode->user_type];
          } else {
            self::createToken($decode->user);
            return $decode->user;
          }
        } catch (Throwable $e) {
          self::type('unauthorized');
          exit(json_encode(["error" => true, "errorMessage" => "Unauthorized. " . $e->getMessage()]));
        } catch (Exception $e) {
          self::type('unauthorized');
          exit(json_encode(["error" => true, "errorMessage" => "Unauthorized. " . $e->getMessage()]));
        }
      } else {
        self::type('authError');
        exit(json_encode(["error" => true, "errorMessage" => "Unauthorized. Use 'Bearer <your_token>'"]));
      }
    } else {
      self::type('authError');
      exit(json_encode(["error" => true, "errorMessage" => "Unauthorized"]));
    }
  }

  public static function options()
  {
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "OPTIONS") {
      header("HTTP/1.1 200 OK");
      die();
    }
  }

  public static function createToken($user,  $user_type = graph::customer, $token_type = graph::access_token, $role = null)
  {
    if ($user_type === graph::customer) {
      $type = $token_type === graph::access_token ? graph::customer_access_token : graph::customer_refresh_token;
    } else {
      $type = $token_type === graph::access_token ? graph::admin_access_token : graph::admin_refresh_token;
    }
    $secretKey = os::fromEnvFile(graph::encryptionKey);
    $host = "localhost"; // https://whizzf.com
    $issuedAt = new DateTimeImmutable();
    if ($token_type == graph::access_token) {
      $expire = $issuedAt->modify('+16 minutes')->getTimestamp();
    } else {
      $expire = $issuedAt->modify('+15 days')->getTimestamp();
    }
    if ($role) {
      $payload = [
        "iat" => $issuedAt->getTimestamp(),
        "nbf" => $issuedAt->getTimestamp(),
        "iss" => $host,
        "exp" => $expire,
        "user" => $user,
        "role" => $role,
        "user_type" => $user_type
      ];
    } else {
      $payload = [
        "iat" => $issuedAt->getTimestamp(),
        "nbf" => $issuedAt->getTimestamp(),
        "iss" => $host,
        "exp" => $expire,
        "user" => $user,
        "user_type" => $user_type
      ];
    }
    $jwt = JWT::encode($payload, $secretKey, 'HS512');
    header("$type:$jwt");
  }

  public static function getHeaders()
  {
    $HTTP_headers = array();
    foreach ($_SERVER as $key => $value) {
      if (substr($key, 0, 5) <> 'HTTP_') {
        continue;
      }
      $single_header = str_replace(' ', '-', str_replace('_', ' ', strtolower(substr($key, 5))));
      $HTTP_headers[$single_header] = $value;
    }
    return $HTTP_headers;
  }

  public static function getKey($key)
  {
    $headers = self::getHeaders();
    return $headers[$key] ?? null;
  }

  public static function allowOrigin($origin = '*')
  {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
  }

  /**
   * type of header to include on request, default = 'json'
   * 
   * @param  mixed $type 'pathError' | 'methodError' | 'authError' | 'unauthorized' | 'unknownError' | 'css' | 'javascript' | 'image' | 'text' | 'pdf' | 'json' | 'xml'
   * @return void
   */
  public static function type($type = 'json')
  {
    switch ($type) {
      case 'json':
        header('Content-Type: application/json; charset=utf-8');
        break;
      case 'pathError':
        header('HTTP/1.0 404 Not Found');
        break;
      case 'methodError':
        header('HTTP/1.0 405 Method Not Allowed');
        break;
      case 'authError':
      case 'unknownError':
        header('HTTP/1.0 400 Bad Request');
        break;
      case 'unauthorized':
        header('HTTP/1.1 401 Unauthorized');
        break;
      case 'css':
        header('Content-Type: text/css');
        break;
      case 'javascript':
        header('Content-Type: text/javascript');
        break;
      case 'image':
        header('Content-Type: image/jpeg');
        break;
      case 'text':
        header('Content-Type: text/plain');
        break;
      case 'pdf':
        header('Content-Type: application/pdf');
        break;
      case 'xml':
        header('Content-Type: text/xml');
        break;
      case 'rss':
        header('Content-Type: application/rss+xml; charset=ISO-8859-1');
        break;
      default:
        header('Content-Type: application/json; charset=utf-8');
        break;
    }
  }
}
