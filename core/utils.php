<?php

namespace core;

use core\plugin\mail\PHPMailer;
use enum\graph;

class utils
{

  private static $response = ['error' => false, 'errorMessage' => null, 'data' => false];

  public static function newOtp()
  {
    $generator = "1357902468";
    $result = "";
    for ($i = 1; $i <= 6; $i++) {
      $result .= substr($generator, (rand() % (strlen($generator))), 1);
    }
    return $result;
  }

  public static function trim($array)
  {
    return array_map(function ($item) {
      return trim($item);
    }, $array);
  }

  /**
   * first key or value of an array
   *
   * @param  array $array
   * @param  boolean $value true, false return array key
   * @return mixed
   */
  public static function first(array $array, $value = true)
  {
    $v = reset($array);
    return $value ? $v :  key($array);
  }


  /**
   * get the last value in an array
   *
   * @param  array $array
   * @return mixed
   */
  public static function last($array)
  {
    return end($array);
  }

  /**
   * build associative array.
   * @param array $args = []
   */
  public static function build_res()
  {
    $arg = func_get_args();
    if (is_array(self::first($arg))) {
      foreach ($arg[0] as $key => $val) {
        self::$response[$key] = $val;
      }
    } else {
      foreach ($arg as $key => $value) {
        if (($key % 2) == 0) {
          self::$response[$value] = $arg[$key + 1];
        }
      }
    }
    return new self;
  }


  /**
   * return array or json built response.
   * @param mixed $type = 'json' | 'array'
   */

  public function get_res($type = 'array')
  {
    if (is_array($type)) {
      self::build_res($type);
      return self::$response;
    }
    if ($type === 'json') {
      header('Content-Type: application/json; charset=utf-8');
      return json_encode(self::$response);
    } else {
      return self::$response;
    }
  }

  public static function response($type = 'array')
  {
    if (is_array($type)) {
      self::build_res($type);
      return self::$response;
    }
    if ($type === 'json') {
      header('Content-Type: application/json; charset=utf-8');
      return json_encode(self::$response);
    } else {
      return self::$response;
    }
  }

  public static function metaphone($string)
  {
    $words = explode(" ", $string);
    $str = '';
    foreach ($words as $word) {
      $str .= metaphone($word) . " ";
    }
    return $str;
  }

  public static function indexing($data = 'string | array', $metaphone = false)
  {
    $str = '';
    $mtf = '';
    if (is_array($data)) {
      foreach ($data as $value) {
        $str .= $value . " ";
      }
    } else {
      $str = $data;
    }
    if ($metaphone) {
      $words = explode(" ", $str);
      foreach ($words as $word) {
        $mtf .= metaphone($word) . " ";
      }
      return $mtf;
    } else {
      return $str;
    }
  }


  /**
   * split
   *
   * @param  string $string = string to seperate
   * @param  string $seperator = seperator string
   * @param  integer $length = array split length to return
   * @return array
   */
  public static function split(string $string, string $seperator = '', $length = null)
  {
    return $length ? explode($seperator, $string, $length) : explode($seperator, $string);
  }

  public static function sanitize($data)
  {
    if (is_string($data)) {
      $data = trim($data);
      $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
      $data = stripslashes($data);
      return $data;
    }
    if (is_array($data)) {
      $buffer = array();
      foreach ($data as $key => $val) {
        $buffer[$key] = self::sanitize($val);
      }
      return $buffer;
    }
    if (is_null($data)) {
      return "NULL";
    }
    if (is_bool($data) || is_int($data)) {
      return (int) $data;
    }
  }

  public static function validate($data)
  {
    foreach ($data as $key => $value) {
      switch ($key) {
        case 'email':
          if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return [graph::error => true, graph::errorMessage => 'Invalid email address'];
          }
          break;
        case "password":
        case "password_again":
        case "old_password":
        case "new_password":
          if (strlen($value) < 6) {
            return [graph::error => true, graph::errorMessage => 'Password must be 6 characters or more'];
          } else if (preg_match('/[^A-Za-z\d@_*#$]+/', $value)) {
            return  [graph::error => true, graph::errorMessage => 'Only alphanumeric and @_#*$ symbols are allowed'];
          }
          break;
        case "phone":
        case "other_phone":
          if (preg_match('/[^0-9\+]+/', (int) substr($value, 1))) {
            return  [graph::error => true, graph::errorMessage => 'Only digit and + symbol are allowed'];
          }
          break;
        default:
          break;
      }
    }
    return [graph::error => false];
  }


  public static function randomNum(int $digits)
  {
    return rand(pow(10, $digits - 1), pow(10, $digits) - 1);
  }


  public static function arrayInArray($array)
  {
    $key = self::first($array, false);
    if (is_numeric($key))
      return true;
    else
      return false;
  }


  public static function uuid($number = 'default')
  {
    // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
    $data = random_bytes(16);
    assert(strlen($data) == 16);

    // Set version to 0100
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    // Output the 36 character UUID.
    if ($number == 'default') {
      return strtoupper(vsprintf('%s%s%s%s%s%s%s%s', str_split(bin2hex($data), 4)));
    } else {
      return strtoupper(vsprintf('%s', str_split(bin2hex($data), (int)$number)));
    }
  }

  /** 
   * @param string month|day|unix $type
   */
  public static function date($type = null)
  {
    date_default_timezone_set("Africa/Lagos");
    if ($type == null) {
      return date("D d-M-Y h:i a");
    } elseif ($type == 'month') {
      return strtolower(date("F"));
    } elseif ($type == 'day') {
      return date("d");
    } elseif ($type == 'unix') {
      $date = new \DateTime("", new \DateTimeZone('Africa/Lagos'));
      return ($date->getTimestamp() + $date->getOffset());
    }
  }

  public static function is_json($string, $return_data = false)
  {
    if (is_array($string)) return false;
    $data = json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE) ? ($return_data ? $data : TRUE) : FALSE;
  }
}
