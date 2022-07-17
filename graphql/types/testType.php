<?php

namespace graphql\types;

use enum\graph;

interface testType
{


  const getUser = [
    graph::input => [
      "id" => graph::integerNotNull,
      "email" => graph::string
    ],
    graph::return => [
      "id" => graph::integer,
      "email" => graph::string,
      "username" => graph::string,
      "first_name" => graph::string,
      "last_name" => graph::string,
      "age" => graph::integer,
      "created_at" => graph::string,
      "address" => [
        graph::type => "getAddress",
        graph::input => [
          "user_id" => "parent.id"
        ]
      ]
    ]
  ];

  const getAddress = [
    graph::input => [
      "id" => graph::integer,
      "user_id" => graph::integer
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

  const getTimeline = [
    graph::input => [
      "id" => graph::integerNotNull,
      "user_id" => graph::integer
    ],
    graph::return => [
      "id" => graph::integer,
      "user_id" => graph::integer,
      "header" => graph::string,
      "detail" => graph::string,
      "created_at" => graph::string,
      "history" => [
        graph::type => "getHistory",
        graph::input => [
          "user_id" => "parent.user_id"
        ]
      ]
    ]
  ];

  const getHistory = [
    graph::input => [
      "id" => graph::integerNotNull,
      "user_id" => graph::integer
    ],
    graph::return => [
      "id" => graph::integer,
      "user_id" => graph::integer,
      "activity" => graph::string,
      "created_at" => graph::string,
      "status" => [
        graph::type => "getStatus",
        graph::input => [
          "user_id" => "parent.user_id"
        ]
      ]
    ]
  ];

  const getStatus = [
    graph::input => [
      "id" => graph::integerNotNull,
      "user_id" => graph::integer
    ],
    graph::return => [
      "id" => graph::integer,
      "user_id" => graph::integer,
      "status" => graph::string,
      "details" => graph::string,
      "created_at" => graph::string,
      "payment" => [
        graph::type => "getPayment",
        graph::input => []
      ]
    ]
  ];

  const getPayment = [
    graph::return => [
      "id" => graph::integer,
      "user_id" => graph::integer,
      "payment_type" => graph::string,
      "amount" => graph::integer,
      "details" => graph::string,
      "created_at" => graph::string
    ]
  ];
}
