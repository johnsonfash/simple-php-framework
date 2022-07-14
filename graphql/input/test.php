<?php

namespace graphql\input;

use enum\graph;

interface test
{
  const editAccountInput = [
    "phone" => graph::string,
    "first_name" => graph::string,
    "last_name" => graph::string,
    "old_password" => graph::string,
    "new_password" => graph::string,
  ];
}
