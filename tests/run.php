<?php

declare(strict_types=1);

require __DIR__ . '/../src/autoload.php';
require __DIR__ . '/TestRunner.php';

$runner = new TestRunner();

require __DIR__ . '/ShipDateParserTest.php';
require __DIR__ . '/CommentClassifierTest.php';
require __DIR__ . '/CategoryTest.php';

exit($runner->summary());
