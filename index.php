<?php
require('./autoload.php');
if (!simple_php_framework_qwertyuiop) {
  exit("Access denied!");
}

use core\auth\header;
use core\http;
use core\router;
use core\view;
use database\db;
use enum\graph;
use handler\typehandler;

header::options();

header::showError();

router::get('/', function () {
  return view::load('view/test.html');
});

router::json('/test', function ($data) {
  return typehandler::start($data);
});

router::json('/admin', function ($data, $user) {
  if ($user->user_type === 'admin') {
    return typehandler::start($data, $user);
  }
}, function () {
  return header::auth(graph::access_token, graph::admin);
});

router::json('/onboarding', function ($data, $valid) {
  if ($valid) {
    return typehandler::start($data);
  }
}, function ($data) {
  return http::onboarding($data);
});

router::post('/upload', function ($id) {
  if ($id) {
    return typehandler::upload($id);
  }
}, function () {
  return header::auth();
});

router::json('/customer', function ($data, $user) {
  if ($user) {
    return typehandler::start($data, $user);
  }
}, function () {
  return header::auth();
});

router::json('/refresh_token', function ($data) {
  return json_encode($data);
});

router::get('/graph', function () {
  return typehandler::view();
});

router::get('/graph/endpoint', function () {
  return typehandler::view(false);
});

router::pathNotFound(function ($path) {
  header('HTTP/1.0 404 Not Found');
  echo 'Error 404 :-(<br>';
  echo 'The requested path "' . $path . '" was not found!';
});

router::methodNotAllowed(function ($path, $method) {
  header('HTTP/1.0 405 Method Not Allowed');
  echo 'Error 405 :-(<br>';
  echo 'The requested path "' . $path . '" exists. But the request method "' . $method . '" is not allowed on this path!';
});

router::run(graph::runDirectory);