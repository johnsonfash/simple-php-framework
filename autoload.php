<?php
define("simple_php_framework_qwertyuiop", true);
spl_autoload_register(function ($classname) {
    include_once dirname(__FILE__) . "/" . str_replace("\\", "/", $classname) . '.php';
});
