<?php

declare(strict_types=1);

use Sweetwater\Comments\Category;
use Sweetwater\Comments\CommentClassifier;
use Sweetwater\Comments\CommentRepository;
use Sweetwater\Database\Connection;

$config = require __DIR__ . '/../src/bootstrap.php';

try {
    $pdo      = Connection::fromConfig($config);
    $comments = (new CommentRepository($pdo))->all();
} catch (\PDOException $e) {
    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><meta charset="utf-8"><title>Database unavailable</title>';
    echo '<h1>Database unavailable</h1>';
    echo '<p>Could not connect to the database. Make sure MySQL is running and the '
       . 'connection settings (or Docker) are in place, then reload.</p>';
    exit;
}

$classifier = new CommentClassifier();

// Bucket and pre-seed comments
$grouped = [];
foreach (Category::displayOrder() as $category) {
    $grouped[$category->value] = [];
}
foreach ($comments as $comment) {
    foreach ($classifier->classify($comment->text) as $category) {
        $grouped[$category->value][] = $comment;
    }
}

/** Escape text for safe HTML output. */
function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Comments Report</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 2rem auto; max-width: 60rem; padding: 0 1rem; color: #1a1a1a; }
        h1 { margin-bottom: 0.25rem; }
        .summary { color: #555; margin-bottom: 2rem; }
        section { margin-bottom: 2.5rem; }
        h2 { border-bottom: 2px solid #ddd; padding-bottom: 0.25rem; }
        .count { color: #888; font-weight: normal; font-size: 0.85em; }
        ul { list-style: none; padding: 0; }
        li { border: 1px solid #e3e3e3; border-radius: 6px; padding: 0.75rem 1rem; margin-bottom: 0.6rem; }
        .order-id { color: #888; font-size: 0.8em; }
        .empty { color: #999; font-style: italic; }
    </style>
</head>
<body>
    <h1>Order Comments Report</h1>
    <p class="summary"><?= count($comments) ?> Comments grouped by topic. A comment may
        appear under more than one section when it covers more than one topic.</p>

    <?php foreach (Category::displayOrder() as $category): ?>
        <?php $items = $grouped[$category->value]; ?>
        <section>
            <h2><?= h($category->heading()) ?> <span class="count">(<?= count($items) ?>)</span></h2>
            <?php if ($items === []): ?>
                <p class="empty">No comments in this section.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($items as $comment): ?>
                        <li>
                            <div class="order-id">Order #<?= $comment->orderId ?></div>
                            <div><?= nl2br(h($comment->text)) ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>
</body>
</html>
