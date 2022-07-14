<?php

namespace database;

use core\os;
use database\drivers\mysql;
use enum\graph;
use Exception;
use mysqli;
use Throwable;


os::dotenv();
class db
{
  protected static $_service;
  protected static $_isConnected = false;

  /**
   * connect to database
   *
   * @return instanceof mysqli
   */
  public static function connect()
  {
    self::$_service = new mysqli(
      graph::dbhost,
      graph::dbusername,
      graph::dbpassword,
      graph::db
    );
    try {
      if (self::$_service->connect_error) throw new Exception("Unable to connect to service");
    } catch (Throwable $e) {
      echo $e->getMessage();
    } catch (Exception $e) {
      echo $e->getMessage();
    }

    self::$_isConnected = true;
    return new self;
  }

  /**
   * disconnect from database
   *
   * @return class
   */
  public function disconnect()
  {
    self::$_isConnected = false;
    self::$_service->close();
    return $this;
  }

  /**
   * encrypt string
   *
   * @param  string $data
   * @return string $ciphertext
   */
  public static function encrypt($data)
  {
    $key = graph::dbencryptionKey;
    $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
    $iv = openssl_random_pseudo_bytes($ivlen);
    $ciphertext_raw = openssl_encrypt($data, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
    $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
    $ciphertext = base64_encode($iv . $hmac . $ciphertext_raw);
    return $ciphertext;
  }

  /**
   * decrypt
   *
   * @param  string $ciphertext
   * @param  string $key = encryption key
   * @return string decripted value
   */
  public static function decrypt($ciphertext, $key = false)
  {
    if (!$key) $key = graph::dbencryptionKey;
    $c = base64_decode($ciphertext);
    $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
    $iv = substr($c, 0, $ivlen);
    $hmac = substr($c, $ivlen, $sha2len = 32);
    $ciphertext_raw = substr($c, $ivlen + $sha2len);
    $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
    $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
    if (hash_equals($hmac, $calcmac)) {
      return $original_plaintext;
    }
  }

  /**
   * query 
   *
   * @return instance  self
   */
  public function query()
  {
    return new mysql($this);
  }

  /**
   * execute returns array of values or boolean if error
   *
   * @param  string $sql = computed sql query
   * @param  array $bind = prepared statement variables
   * @return mixed array | boolean
   */
  public function execute($sql, $bind = [])
  {
    $dataType = "";
    if (!empty($bind)) {
      foreach ($bind as $val) {
        $dataType .= is_int($val) ? "i" : "s";
      }
    }
    $resp = self::$_service->prepare($sql);
    if (!empty($bind)) {
      $resp->bind_param($dataType, ...$bind);
    }
    $resp->execute();
    $data = $resp->get_result();
    if (!$data) $data = (bool) $resp->affected_rows;
    return $data;
  }

  /**
   * execute2
   *
   * @param  string $sql = query string to be executed 
   * @return array = array of values from database
   */
  public function execute2($sql)
  {
    $result = self::$_service->query($sql);
    $rows = array();
    for ($i = 0; $i < $result->num_rows; $i++) {
      $rows[] = $result->fetch_array(MYSQLI_ASSOC);
    }
    return $rows;
  }


  /**
   * perform raw mysql query
   *
   * @param  string $sql = sql query string to be executed
   * @return array = array of values from database
   */
  public function raw($sql)
  {
    return $this->execute2($sql);
  }

  public function clearTable($table)
  {
    $this->__query("DELETE FROM $table");
    $this->__query("ALTER TABLE $table AUTO_INCREMENT = 0;");
  }

  /**
   * __query
   *
   * @param  string $sql = sql query string to be executed
   * @return instance  = database after executing sql string
   */
  public function __query($sql)
  {
    return self::$_service->query($sql);
  }

  /**
   * escape database string
   *
   * @param  string $val = string to be excaped
   * @return string
   */
  public function escape($val)
  {
    return self::$_service->real_escape_string($val);
  }

  /**
   * returns the primary key of the old (and changed) data row
   *
   * @return integer
   */
  public function getLastInsertId()
  {
    return self::$_service->insert_id;
  }

  /**
   * returns the number of rows changed, deleted, or inserted by the last statement
   *
   * @return integer
   */
  public function getAffectedRow()
  {
    return self::$_service->affected_rows;
  }

  /**
   * returns the last error description
   *
   * @return string
   */
  public static function error()
  {
    return self::$_service->error;
  }


  /**
   * verify password / encrypt new password / get ['status','registration']
   *
   * @param  string $table = database table
   * @param  string $email = user email address
   * @param  string $password = current input password to check
   * @param  string $newPassword = can be string 'login' or the new password to encrypt
   * @param  string $passFrmDB  = if you already aquired the password from database
   * @return array = ["error"=> true | false, "errorMessage"=> null | string]
   */
  public static function verifyPassword($table, $email, $oldPassword = '[password]', $newPassword = false, $dataFromDB = false)
  {
    $response[graph::error] = false;
    $response[graph::logoutUser] = false;

    if ($dataFromDB) {
      $data[graph::password] = $dataFromDB['password'];
      $data[graph::status] = $dataFromDB['status'];
    } else {
      $db = self::connect();
      $data = $db->query()->table($table, array(graph::password, graph::status))->where("email", $email)->first();
      empty($data) ? $response[graph::error] = true : $response[graph::error] = false;
    }
    if ($response[graph::error]) {
      $response[graph::errorMessage] = 'invalid email or password';
    } else {
      if ($data[graph::status] === 'inactive') {
        $response[graph::error] = true;
        $response[graph::errorMessage] = "Your account is inactive, please contact our customer service.";
      }
      $response[graph::errorMessage] = "";
      $dbPassword = self::decrypt($data[graph::password]);
      if ($oldPassword === $dbPassword) {
        // $response[graph::status] = $data[graph::status];
        if ($newPassword) {
          $response[graph::encPassword] = self::encrypt($newPassword);
        }
      } else {
        $response[graph::error] = true;
        $response[graph::errorMessage] = "Invalid email or password";
      }
    }
    return $response;
  }
}
