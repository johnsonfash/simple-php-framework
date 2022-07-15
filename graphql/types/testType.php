<?php

namespace graphql\types;

use enum\graph;

interface testType
{
  const getUser = [
    graph::input => [
      "id" => graph::integerNotNull,
    ],
    graph::return => [
      "id" => graph::integer,
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
      "id" => graph::integer,
      "history" => [
        graph::type => "getHistory",
        graph::input => [
          "id" => "parent.id"
        ]
      ]
    ]
  ];

  const getHistory = [
    graph::input => [
      "id" => graph::integerNotNull,
    ],
    graph::return => [
      "id" => graph::integer,
      "status" => [
        graph::type => "getStatus",
        graph::input => [
          "id" => "parent.id"
        ]
      ]
    ]
  ];

  const getStatus = [
    graph::input => [
      "id" => graph::integerNotNull,
    ],
    graph::return => [
      "id" => graph::integer,
      "status" => [
        graph::type => "getPayment",
        graph::input => [
          "id" => "parent.id"
        ]
      ]
    ]
  ];

  const getPayment = [
    graph::input => [
      "id" => graph::integerNotNull,
    ],
    graph::return => [
      "id" => graph::integer,
      "status" => [
        graph::type => "getRemit",
        graph::input => [
          "id" => "parent.id"
        ]
      ]
    ]
  ];

  const getRemit = [
    graph::input => [
      "id" => graph::integerNotNull,
    ],
    graph::return => [
      "id" => graph::integer,
      "status" => [
        graph::type => "getRemit2",
        graph::input => [
          "id" => "parent.id"
        ]
      ]
    ]
  ];

  const getRemit2 = [
    graph::input => [
      "id" => graph::integerNotNull,
    ],
    graph::return => [
      "id" => graph::integer,
      "date" => graph::string
    ]
  ];
}
