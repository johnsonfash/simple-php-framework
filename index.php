<?php

use core\auth\header;
use core\http;
use core\router;
use core\view;
use database\db;
use enum\graph;
use handler\typehandler;

use function PHPSTORM_META\type;

header::options();

header::showError();


// try {
//   throw new Exception('I was throw', 400);
// } catch (\Throwable $e) {
//   echo json_encode(['error' => true, 'errorMessage' => $e->getMessage(), 'errorCode' => $e->getCode()]);
// }


router::get('/', function () {
  return view::load('test.html');
});

router::json('/test', function ($request) {
  $type = typehandler::getConstant('static', 'getUser');
  // $retn_handler = typehandler::returnCheck2($type['data']['return'], $request->query->return);
  $retn_handler = typehandler::inputCheck($type['data']['input'], $request->variables);
  return json_encode($retn_handler);
  return json_encode($type);
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


//local
router::run('/simple-php-framework/');

//web
// router::run('/');