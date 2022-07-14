<?php

namespace graphql\types;

use enum\graph;

interface testType
{
  const getUser = [
    graph::input => [
      "id" => graph::integerNotNull
    ],
    graph::return => [
      "id" => graph::integer,
      "name" => graph::string,
      "age" => graph::integer,
      "address" => [
        graph::type => "getAddress",
        graph::input => [
          "id" => "parent.id"
        ]
      ]
    ]
  ];

  const getAddress = [
    graph::input => [
      "id" => graph::integerNotNull
    ],
    graph::return => [
      "id" => graph::integer,
      "country" => graph::string,
      "state" => graph::string,
      "lga" => graph::string,
      "geodata" => [
        graph::type => "getGeoData",
        graph::input => [
          "id" => "parent.id"
        ]
      ]
    ]
  ];


  const getGeoData = [
    graph::input => [
      "id" => graph::integerNotNull
    ],
    graph::return => [
      "id" => graph::integer,
      "lat" => graph::string,
      "lng" => graph::string,
      "bio" => [
        graph::type => "getBio",
        graph::input => [
          "id" => "parent.id"
        ]
      ]
    ]
  ];

  const getBio = [
    graph::input => [
      "id" => graph::integerNotNull
    ],
    graph::return => [
      "id" => graph::integer,
      "intro" => graph::string,
      "age" => graph::integer,
      "timeline" => [
        graph::type => "getTimeline",
        graph::input => [
          "id" => "parent.id"
        ]
      ]
    ]
  ];

  const getTimeline = [
    graph::input => [
      "id" => graph::integerNotNull,
    ],
    graph::return => [
      "date" => graph::string,
      "status" => graph::string
    ]
  ];
}
