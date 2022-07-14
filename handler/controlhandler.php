<?php

namespace handler;

use controllers\test;
use core\graphql\control;

/**
 * controlhandler handles all queries in batches
 * dispatch only one inner matrix method for now
 */
class controlhandler extends control
{
  use  test;
}
