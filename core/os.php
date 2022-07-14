<?php

namespace core;

class os
{
  public static function fromEnvFile($name = null)
  {
    self::dotenv();
    return self::env($name);
  }
  
  public static function env($name)
  {
    return getenv($name) ?? null;
  }

  public static function dotenv($path = './.env')
  {
    if (!is_readable(realpath($path))) {
      return;
    }
    $lines = file(realpath($path), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
      if (strpos(trim($line), '#') === 0) {
        continue;
      }
      list($name, $value) = explode('=', $line, 2);
      $name = trim($name);
      $value = trim($value);
      if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
        putenv(sprintf('%s=%s', $name, $value));
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
      }
    }
  }
}
