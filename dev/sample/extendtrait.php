<?php

namespace dev\sample;

trait one
{
  protected static function a($v, $c)
  {
    echo 'a' . $v . $c;
  }
}

trait two
{
  public function b()
  {
    echo 'b';
  }
}

class join
{
  use one, two;
  public function c($v = '', $d = '')
  {
    echo self::a($v, $d);
  }
}

// $d = new join();
// $c = 'c';
// echo join::$c();

$d = new join();
$c = 'c';
echo $d->$c();
// echo $d::$c();

//static method
call_user_func(['join', 'c'], ' boy', ' girl');

$d = new join();

// method
call_user_func([$d, 'c'], ' boy', ' girl');




  // public static function createControllers()
  // {
  //   $implement = class_implements(new self);
  //   $r =  json_encode($implement);
  //   foreach ($implement as $value) {
  //     $controllers[] = utils::last(explode('\\', $value));
  //   }
  //   $t = utils::last($implement);
  //   $v = new \ReflectionClass($t);
  //   $v = $v->getConstants();
  //   return json_encode($v);
  //   // return $controllers[0];
  //   // return json_encode(self::getTypes($controller[0]));
  //   // return json_encode($controller);
  //   // foreach ($controller as $key => $value) {
  //   // }
  // }