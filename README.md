<p align="center"><br/><br/><img src="./uploads/simple-php.png" alt="simple-php.png" width="374" height="72"></p>

# About Simple-php-framework

Simple php framework is a light weight (< 1mb), expressive, easy to configure, and less ambiguous framework. It comes packed with most things you need and can be easily extended to cater your needs through its simple syntax and vanilla codebase. This was built to tackle one problem at start, and that was a dynamic way to create RestApi using some ideas from GraphQL and just make api calls more fun and easy to setup.

In a traditional app, you will need to create several routes just to fetch data. With simple-php-framework, it creates a graph of all your routes so you can fetch related information from different tables, records etc with just one request and one endpoint.

Another advantage of using simple-php-framework is its views component. You do not need to use custom tags i.e blade syntax inside a php file anymore, just use the variable(s) assigned to the script and you are good to go, example shown below.

This package contains:

1.  Simple and fast routing mechanism for json, post and get request.
2.  Easy to use views loader for public / private  files
3.  Simple but powerful Authentication mechanism i.e header control, os, jwt and more.
4.  Expressive database ORM
5.  Graph mapping / live documentation / schema of all Apis.
6.  Out of the box utilities for file upload, compression, complex to simple data manipulation functions, agonistic mail functionalities, mail templates and more

Simple php framework lets you build robust applications.

# Learning - Simple php framework MVC

## 1\. `index.php`

Every request gets routed to the `index.php` page by default. So all routes, auth guards and more can be configured here.

There is a `createHTACCESS();` function that creates a dynamic `.htaccess` file for you so that the right part to autoload script is used at all times.

During deployment to a new server, local etc, make sure to add this function at the top of index.php script, to set up these path correctly.

In `index.php`

```php
<?php

createHTACCESS();

.......
```

### <ins>Steps</ins>

- Edit the file in `apache_file.txt` to suit your needs.
- Include `createHTACCESS();` function at the start of `index.php`
- Deploy your project
- The composer.json is just to tell the server that you are using php scripting language i.e heroku, digital ocean deploy
- Make sure to give proper permissions to the folder, uploads directory and editable folders, index.php with `read & write access` if necessary / under a firewall.
- Navigate to your url  index page so the function can run itself.
- This function creates the `.htaccess` with the right path to `autoload.php` and deletes itself from `index.php` afterwards by default. Reload your browser to see the effect.

**Local deploy:**

- Clone project to `xampp/lampp` `./htdocs` by opening a terminal in the directory, then `git clone https://github.com/johnsonfash/simple-php-framework.git`
- To open up power shell on htdocs, navigate to htdocs, ctrl + shift +right click on the folder.
- On mac, navigate to htdocs, double finger touch on the folder to open the menu list, and click open terminal on folder.
- Create a database with the name `simple-db`, or change the `const db` from `enum/graph.php`.
- Import the `sample-db.sql` file, and follow the steps given above. Open up `view/test.html` on your browser, i.e `http://localhost/simple-php-framework` and then `inspect` to see the logged result.


Sample of `index.php` routes

```php
header::options(); //import for api request with OPTIONS i.e upload to send out a status "OK" to continue

router::get('/login', function () {
  $data = ['a'];
  return view::load('view/login.php', $data);  // simply use the variable $data inside of login.php without issues.
});

router::json('/customer', function ($data, $user_id) {
  if ($user_id) {
    return typehandler::start($data, $user);  //typehandler handles all graphed apis by using one route.
  }
}, function () {
  return header::auth(); //authenticates a user using i.e jwt and passes the details to main function i.e $user; 
});

router::post('/upload', function ($id) {
  if ($id) {
    return typehandler::upload($id); //upload files handler
  }
}, function () {
  return header::auth();
});

router::get('/graph', function () {
  return typehandler::view(); //api documentation. Very useful for frontend developers
});
```

## 2\. `.htaccess`

You can simply use an **apache** / **nginx** config file instead of `.htaccess`, but for those using shared hosting, this will be quite useful.

The default **.htaccess** file comes with the basic configuration to get started.

- Reroute of all request to index.php.
- Configurable access control you can tweak to manage all Api access
- Allowed headers, and exposed headers which can be useful for token and jwt on the frontend.

Lets start by defining the functionality of each one

**1\. php\_value auto\_prepend_file**

This prepends autoload.php so you don't have to. It lets you simply use namespaces and use keywords to call classes with a more defined and  agnostic syntax than using include or require, see these articles to know more: [namespace](https://www.php.net/manual/en/language.namespaces.php), [use](https://www.php.net/manual/en/language.namespaces.importing.php), [php autoprepend file](https://stackoverflow.com/questions/9045445/auto-prepend-php-file-using-htaccess-relative-to-htaccess-file), [htaccess](https://www.geeksforgeeks.org/what-is-htaccess-file-in-php/),  [htaccess example](https://www.php.net/manual/en/yaf.tutorials.php).

**2\. Header always set Access-Control-Expose-Headers**

This lets you attach custom headers to api request, i.e the frontend can have access to read backend headers like token, jwt etc. This is incredibly useful to separate the returned api data from **token**, **jwt**, **cookies** and more which should sit at the head anyways, check information [here](https://stackoverflow.com/questions/25673089/why-is-access-control-expose-headers-needed) for more.

## 3\. `enum/graph.php`

You can defined all the constants you will use throughout the app here. One advantage of this is the autocomplete feature, if you use the right IDE i.e vs-code.

## 4\. The MVC pattern

The model, controllers, and views folders lets you define:

- Model - database function calls that returns a result
- Controller -  handles the frontend input, calls the right model function, manipulate data and sends the response back to the user.
- View - handles loading a script to view, attaching variables with data, so they can be used within the view script.

Samples are shown in each folder to get started.

## 5\. The `./core` folder

The core is where the magic happens. auth, plugin, http request function, mail, jwt, os methods, utils, upload, router, graphql validation, assignment methods to right api and more are here. Codebase is straight forward and can be configured to suit your needs.

## 6\. THe Database folder

Database drivers are defined here.

By default, this comes with `mysqli` [ORM](https://stackoverflow.com/questions/1279613/what-is-an-orm-how-does-it-work-and-how-should-i-use-one) to get started, but you can easily configure it to support `postgres` and more.

**NOTE:  for security, every `query()` apart from `raw();` uses prepared statement. While data are sanitised for all request by default, be sure not to include frontend user input in `raw();` unless you are sure of what you are doing.**

Samples below:

```php
use database\db;

$db = db::connect();
$get_a_user = $db->query()->table('customers', ['email', 'first_name','created_at'])->where('id', 1)->first();

$insert_a_ser = $db->query()->table('customers')->insert([
      'email' => $email, 'first_name' => 'john'
    ]);
    
$get_all_users = $db->query()->table('customers', array $columns)->getAll();

$update_a_user = $db->query()->table('customers')->where('id', $id)->update([
      'first_name' => $first_name
    ]);
    
$limit_and_order = $db->query()->table('otp')->where('otp', $otp, 'type', 'customer_password', 'user_type', 'customer')->orderBy('id', 'desc')->limit(1)->first();

$limit_and_order_type_2 = $db->query()->table('otp')->where(['otp' => $otp, 'type' => 'customer_password', 'user_type' => 'customer'])->orderBy('id', 'desc')->limit(1)->first();

$delete = $db->query()->table('otp')->where('otp', $variables->otp)->delete();

$pagination = $db->query()->table('customers')->where('email LIKE', '@gmail')->limit($variables->size, $variables->page)->orderBy('id', 'DESC')->getAll();

$dangerous_raw_query_to_array_return = $db->raw("SELECT COUNT(CASE WHEN status = 'active' THEN 1 END) as 'active', COUNT(CASE WHEN status = 'blocked' THEN 1 END) as 'blocked', COUNT(CASE WHEN status = 'pending' THEN 1 END) as 'pending' FROM customers WHERE user_type =  'starters'");
```

## 7\. The `./handler` folder

The ./handler directory holds the `controlhandler.php` & `typehandler.php`.

Make sure to register all your **controller classes** in the `./handler/controlhandler.php`

Also register all your **graphql/types** script in the `./handler/typehandler.php`

Samples have been prepared to give you a good understanding how this works.

## 8\. The `graphql/types` folder

The graphql types folder is where you define your api schema, make minute input validations and return schema. A sample is show below.

By default, the variable name of every type constant must match the name of a controller function to execute. Which means every type must go along with a class function that execute them, examples are given in the `controllers` and `graphql/types` folders respectively.

**NOTE:** Make sure the variable name of type constants are unique, else, an error.

You can also setup [constraints](https://www.w3schools.com/sql/sql_constraints.asp#:~:text=Constraints%20are%20used%20to%20limit,column%20level%20or%20table%20level.) on the database to make this nested queries even better.

```php
<?php

namespace graphql\types;

use enum\graph;

interface testType
{


  const getUser = [
    graph::input => [
      "id" => graph::integerNotNull,  //a required input field to execute this function
    ],
    graph::return => [
      "id" => graph::integer,
      "email" => graph::string,
      "username" => graph::string,
      "first_name" => graph::string,
      "last_name" => graph::string,
      "age" => graph::integer,
      "created_at" => graph::string,
      "address" => [ //nexted query within this type, 
        graph::type => "getAddress",
        graph::input => [
          "user_id" => "parent.id" //parent means the main thread value i.e ['id'=> 1, 'email' => 'fashanutosin7@gmail.com' ...]. you are mapping this id from parent to input parameter of the next query type. The nexted query must have an input field called 'user_id'
        ]
      ]
    ]
  ];

  const getAddress = [
    graph::input => [
      "id" => graph::integerNotNull,
      "user_id" => graph::integer // meaning you cant make direct api call to this without defining the id input, but user_id is optional. Only backend chain call can omit this feature, i.e getUser attach getAddress without calling id
    ],
    graph::return => [
      "id" => graph::integer,
      "user_id" => graph::integer,
      "country" => graph::string,
      "state" => graph::string,
      "lga" => graph::string,
      "address" => graph::string,
      "created_at" => graph::string,
      "geodata" => [
        graph::type => "getGeoData",
        graph::input => [
          "address_id" => "parent.id"
        ]
      ]
    ]
  ];
  
  
  const getGeoData = [
    graph::input => [
      "id" => graph::integerNotNull,
      "address_id" => graph::integer
    ],
    graph::return => [
      "id" => graph::integer,
      "address_id" => graph::integer,
      "user_id" => graph::integer,
      "lat" => graph::string,
      "lng" => graph::string,
      "created_at" => graph::string,
      "bio" => [
        graph::type => "getBio",
        graph::input => [
          "user_id" => "parent.user_id"
        ]
      ]
    ]
  ];

  const getBio = [
    graph::input => [
      "id" => graph::integerNotNull,
      "user_id" => graph::integer
    ],
    graph::return => [
      "id" => graph::integer,
      "user_id" => graph::integer,
      "user_type" => graph::string,
      "bio" => graph::string,
      "likes" => graph::string,
      "created_at" => graph::string,
      "timeline" => [
        graph::type => "getTimeline",
        graph::input => [
          "user_id" => "parent.user_id"
        ]
      ]
    ]
  ];
  
  const getTimeline = .......
```

## 9\. Controllers

By default, every controller has a `$parent` value (if called as a nested query or empty array: `[]`, if its the main query. `$columns` :(required columns defined from the frontend), `$variables` which can be `[]` or defined by the frontend if needed for the request. `$middleware_data` can be meta data like `user_id` from authenticated route or `null` for public routes.

By default every controller must return an `array` which must include a `'data'` associative key. It is recommended you use the `build_res` utility function offered in the test.php sample to build your response object, and also `graph::data`, `graph::error`, `graph::errorMessage` for autocomplete features and avoiding mistakes.

[Traits](https://www.php.net/manual/en/language.oop5.traits.php) are used instead of classes for good implementation reasons. Make use of trait in controllers.

```php
<?php

namespace controllers;

use core\utils;
use enum\graph;
use model\user as ModelUser;

trait user
{
  public static function getUser($parent, $columns, $variables, $middleware_data)
  {
    $res = utils::build_res();


    $input = utils::validate($variables);

    if ($input[graph::error]) {
      return $res->get_res($input);
    }

    $model = new ModelTest();

    //just a sample, might not be need here, or overkill
    $old_user = $model->checkEmail(@$variables->email);

    $user = $model->getUser($variables->id, $columns);

    return $res->get_res([graph::data => $user]);
  }

......
 }
```

## 10\. Making RestApi calls

Making api request is simple and straight forward, simply goto the `PUBLIC_URL/graph` to see the api `type`, `input` and `return` values to make your api call.

The `/graph` shows all the available `endpoints` to the graph, the `input` parameters and `return` values. Sample below:

```javascript
{
  "description": {
    "maximum": "you can set the maximum nested query in the enum 'maxNextedQuery'",
    "requirement_1": "nexted queries should include type & optional input",
    "note_1": "please make sure to include & handle all type of input for both main queries and nexted queries",
    "note_2": "you can choose to include a description & name key on your API service type if you want",
    "warning_1": "auth fields like password and token must be managed internally and not exposed to the GRAPH VIEW return keys",
    "type": "used to specify the controller for a nexted query",
    "input": "used map input from the main query to the input of the nexted query e.g [next_query.input => main_query.input]"
  },
  "types": {
    "getAddress": {
      "input": {
        "id": "integer",
        "user_id": "integer"
      },
      "return": {
        "id": "integer",
        "user_id": "integer",
        "country": "string",
        "state": "string",
        "lga": "string",
        "address": "string",
        "created_at": "string",
        "geodata": {
          "id": "integer",
          "address_id": "integer",
          "user_id": "integer",
          "lat": "string",
          "lng": "string",
          "created_at": "string",
          "bio": {
            "id": "integer",
            "user_id": "integer",
            "user_type": "string",
            "bio": "string",
            "likes": "string",
            "created_at": "string",
            "timeline": {....                  }
                }
              }
            }
          }
        }
      }
    },
    "getBio": {
      "input": {
        "id": "integer!",
        "user_id": "integer"
      },
.......
  }
}
```

Check the sample below on making a rest api request, or goto `view/test.html` to see a sample

```javascript
const variables = { id: 1 };

      const query = {
        type: "getUser",
        return: {
          id: "integer",    //you can use short(1 letter) or long key type. i.e "string" === "s", "boolean" === "b"
          email: "string",
          username: "string",
          first_name: "s",
          last_name: "s",
          age: "i",
          created_at: "s",
          address: {
            id: "i",
            country: "s",
            state: "s",
            lga: "s",
            address: "s",
            geodata: {
              id: "i",
              lat: "s",
              lng: "s",
              bio: {
                id: "i",
                bio: "s",
                likes: "s",
                timeline: {
                  id: "i",
                  header: "s",
                  detail: "s",
                },
              },
            },
          },
        },
      };

      fetch("http://localhost/simple-php-framework/test", {
        method: "post",
        headers: {
          "content-Type": "application/json",
        },
        body: JSON.stringify({ query, variables }),
      })
        .then((d) => {
          return d.json();
        })
        .then((v) => console.log(v))
        .catch((e) => console.log(e.message));
```