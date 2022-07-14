<?php

namespace dev\sample;

use core\auth\jwt\JWT;
use core\auth\jwt\Key;
use DateTimeImmutable;

include './core/jwt/JWT.php';
include './core/jwt/BeforeValidException.php';
include './core/jwt/ExpiredException.php';
include './core/jwt/SignatureInvalidException.php';
include './core/jwt/JWK.php';
include './core/jwt/Key.php';

$secretKey  = 'bGS6lzFqvvSQ8ALbOxatm7/Vk7mLQyzqaS34Q4oR1ew=';
$issuedAt   = new DateTimeImmutable();
$expire     = $issuedAt->modify('+16 minutes')->getTimestamp();

$payload = [
  "iat" => $issuedAt->getTimestamp(),
  "nbf" => $issuedAt->getTimestamp(),
  "iss" => "localhost",
  "exp" => $expire,
  "user" => "tosin"
];

$data = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE2NTQ1MzE0MDMsIm5iZiI6MTY1NDUzMTQwMywiaXNzIjoibG9jYWxob3N0IiwiZXhwIjoxNjU0NTMyMzAzLCJ1c2VyIjoidG9zaW4ifQ.sJZV1SvFYXIDB2hlacimDB_P8T3v8ZgrbuegC_o-2Me6Gm7KVPEEMd8k6VJDo_VSMgjSX4Uf4rM0Smgj7E1ciQ';


$jwt = JWT::encode($payload, $secretKey, 'HS512');








JWT::$leeway += 60;
try {
  $decode = JWT::decode($jwt, new Key($secretKey, 'HS512'));
  echo json_encode($decode);
} catch (\Exception $e) {
  echo $e->getMessage();
}
// echo $jwt;
