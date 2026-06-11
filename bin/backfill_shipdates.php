<?php

declare(strict_types=1);

use Sweetwater\Comments\CommentRepository;
use Sweetwater\Database\Connection;
use Sweetwater\ShipDate\ShipDateParser;

$config = require __DIR__ . '/../src/bootstrap.php';

$pdo    = Connection::fromConfig($config);
$repo   = new CommentRepository($pdo);
$parser = new ShipDateParser();

$updated = 0;
$skipped = 0;

$pdo->beginTransaction();

try {
    foreach ($repo->all() as $comment) {
        $date = $parser->parse($comment->text);

        if ($date === null) {
            $skipped++;
            continue;
        }

        $repo->updateShipDate($comment->orderId, $date);
        $updated++;
    }

    $pdo->commit();
} catch (\Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, "Backfill failed: {$e->getMessage()}\n");
    exit(1);
}

echo "Backfill complete. Updated {$updated}, skipped {$skipped} (no ship date found).\n";
