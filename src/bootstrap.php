<?php

declare(strict_types=1);

/**
 * Standard bootstrap, script only needs:
 *     $config = require __DIR__ . '/../src/bootstrap.php';
 */

require __DIR__ . '/autoload.php';

use Sweetwater\Config\Config;

return Config::load(dirname(__DIR__));
