<?php

/**
 * This file is an alias to /content.css basically,
 * but it is required for when WWW_URL is on a different domain than ADMIN_URL
 */

namespace Garradin;

use Garradin\Web\Skeleton;

require_once __DIR__ . '/../_inc.php';

$s = new Skeleton('content.css');
$s->serve();
