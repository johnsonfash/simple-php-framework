<?php

namespace enum;

interface graph
{

  //local
  const dbhost = "localhost";
  const dbdriver = "mysqli";
  const dbencryptionKey = "qwertyuiop";
  const dbusername = "root";
  const db = "simple-db";
  const dbpassword = "";
  const flutterKey = "FLWSECK_TEST-xxxxxxxxxxxxxxxxxxxxx347-P";
  const uploadFullPath = 'http://localhost/simple-php-framework/uploads/';
  const runDirectory = '/simple-php-framework/';
  const url = 'http://localhost/simple-php-framework';
  const adminUrl = 'http://localhost/simple-php-framework';
  const enableEmail = false;

  //web
  // const dbhost = "localhost";
  // const dbdriver = "mysqli";
  // const dbencryptionKey = "qwertyuiop";
  // const dbusername = "simple-php-framework";
  // const db = "simple-php-framework";
  // const dbpassword = "http://simple-php-framework.com";
  // const flutterKey = "FLWSECK_TEST-xxxxxxxxxxxxxxxxxxxxx347-P";
  // const uploadFullPath = 'http://simple-php-framework.com/uploads/';
  // const runDirectory = '/';
  // const url = 'http://localhost/simple-php-framework';
  // const adminUrl = 'http://localhost/simple-php-framework';
  // const enableEmail = true;


  const company_email = 'support@simple-php-framework.com';
  const company_phone = '+2349036723177';
  const company_address = '68 Johnston street, Sunnyside Gauteng, 0002. Pretoria, Nigeria.';
  const notNull = '!';
  const FILES = 'FILES';
  const FILE_QUERY = 'FILE_QUERY';
  const encPassword = 'encPassword';
  const password = 'password';
  const status = 'status';
  const controller = "controller";
  const variables = "variables";
  const keys = "keys";
  const encryptionKey = "encryptionKey";
  const Authorization = 'Authorization';
  const error = 'error';
  const trace = 'trace';
  const customer = 'customer';
  const admin = 'admin';
  const maxNextedQuery = 10;
  const admin_refresh_token = 'simple_refresh_token';
  const admin_access_token = 'simple_access_token';
  const customer_access_token = 'simple_customer_access_token';
  const customer_refresh_token = 'simple_customer_refresh_token';
  const access_token = 'access_token';
  const refresh_token = 'refresh_token';
  const errorType = 'errorType';
  const logoutUser = 'logoutUser';
  const data = 'data';
  const errorMessage = 'errorMessage';
  const values = "values";
  const alias = 'alias';
  const meta = "meta";
  const name = "name";
  const path = 'path';
  const input = "input";
  const type = "type";
  const return = "return";
  const integerOrString = 'integer|string';
  const integerOrStringNotNull = 'integer!|string!';
  const integerOrDouble = 'integer|double';
  const integerOrDoubleNotNull = 'integer!|double!';
  const boolean = 'boolean';
  const booleanNotNull = 'boolean!';
  const array = 'array';
  const arrayNotNull = 'array!';
  const integer = 'integer';
  const integerNotNull = 'integer!';
  const object = 'object';
  const objectNotNull = 'object!';
  const string = 'string';
  const stringNotNull = 'string!';
  const null = 'null';
  const float = 'float';
  const floatNotNull = 'float!';
  const double = 'double';
  const doubleNotNull = 'double!';
  const types = [
    "array", "a",
    "boolean", "b",
    "double", "d",
    "float", "f",
    "integer", "i",
    "string", "s",
    "null", "n",
    "object", "o"
  ];
}
