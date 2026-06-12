<?php

declare(strict_types=1);

use Sweetwater\Database\Connection;

$config = require __DIR__ . '/../src/bootstrap.php';

$pdo = Connection::fromConfig($config);

// Revert shipdate_expected to the supplied 0000-00-00 so the fill can be run
// again. MySQL 8 rejects zero dates under strict mode, so relax sql_mode here.
$pdo->exec("SET SESSION sql_mode = ''");
$rows = $pdo->exec("UPDATE sweetwater_test SET shipdate_expected = '0000-00-00 00:00:00'");

echo "Reset complete. {$rows} row(s) set back to 0000-00-00.\n";
