<?php

namespace core;

class view
{
  public static function load(string $viewFile, string $variableName  = '', $variableData = '')
  {
    if ($variableName) {
      if (strpos($variableName, '$') !== false) $variableName = substr($variableName, 1);
      eval('$$variableName = $variableData;');
    }
    @include  $viewFile . (strpos($viewFile, '.') ? '' : '.php');
  }
}
