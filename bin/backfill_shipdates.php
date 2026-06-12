<?php

declare(strict_types=1);

use Sweetwater\Comments\CommentRepository;
use Sweetwater\Database\Connection;
use Sweetwater\ShipDate\ShipDateBackfiller;
use Sweetwater\ShipDate\ShipDateParser;

$config = require __DIR__ . '/../src/bootstrap.php';

$repo   = new CommentRepository(Connection::fromConfig($config));
$filled = (new ShipDateBackfiller($repo, new ShipDateParser()))->run();

echo "Backfill complete. Filled {$filled} ship date(s) from the comments.\n";
