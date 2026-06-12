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

$grouped = [];
foreach (Category::displayOrder() as $category) {
    $grouped[$category->value] = [];
}
foreach ($comments as $comment) {
    $categories = $classifier->classify($comment->text);
    $grouped[$categories[0]->value][] = ['comment' => $comment, 'categories' => $categories];
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
        .chips { margin: 0.3rem 0 0.5rem; }
        .chip { display: inline-block; padding: 0.1rem 0.55rem; border-radius: 999px; background: #eee; color: #444; font-size: 0.72em; margin-right: 0.3rem; }
        .chip-best { background: #1a1a1a; color: #fff; font-weight: bold; }
        .ship-date { font-size: 0.85em; margin-top: 0.6rem; padding-top: 0.5rem; border-top: 1px solid #e3e3e3; }
        .date-missing { color: #c1121f; font-weight: bold; }
        .empty { color: #999; font-style: italic; }
        .filters { display: flex; gap: 1.5rem; align-items: center; flex-wrap: wrap; margin-bottom: 2rem; }
        .filter { position: relative; }
        .filter > button { font: inherit; padding: 0.35rem 0.75rem; border: 1px solid #bbb; border-radius: 4px; background: #fff; cursor: pointer; }
        .filter-panel { position: absolute; top: 100%; left: 0; margin-top: 0.25rem; background: #fff; border: 1px solid #bbb; border-radius: 4px; padding: 0.5rem 0.9rem; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12); z-index: 10; white-space: nowrap; }
        .filter-panel label { display: block; padding: 0.2rem 0; }
        .filters select { font: inherit; padding: 0.3rem; }
        .filters label { font-size: 0.9em; color: #444; }
    </style>
</head>
<body>
    <h1>Order Comments Report</h1>
    <p class="summary"><?= count($comments) ?> comments, each grouped under its best-match
        topic. Tags show every topic a comment matches; the filled tag is the best match.</p>

    <div class="filters">
        <div class="filter">
            <button type="button" id="cat-toggle">Categories &#9662;</button>
            <div class="filter-panel" id="cat-panel" hidden>
                <label><input type="checkbox" id="cat-all" checked> All</label>
                <?php foreach (Category::displayOrder() as $category): ?>
                    <label><input type="checkbox" class="cat-option" value="<?= h($category->value) ?>" checked> <?= h($category->heading()) ?></label>
                <?php endforeach; ?>
            </div>
        </div>
        <label>Ship date Provided:
            <select id="date-filter">
                <option value="all">All</option>
                <option value="present">Date Present</option>
                <option value="missing">Date Missing</option>
            </select>
        </label>
    </div>

    <?php foreach (Category::displayOrder() as $category): ?>
        <?php $items = $grouped[$category->value]; ?>
        <section data-category="<?= h($category->value) ?>">
            <h2><?= h($category->heading()) ?> <span class="count">(<?= count($items) ?>)</span></h2>
            <?php if ($items === []): ?>
                <p class="empty">No comments in this section.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($items as $item): ?>
                        <?php $comment = $item['comment']; ?>
                        <li data-has-date="<?= $comment->expectedShipDate !== null ? '1' : '0' ?>">
                            <div class="order-id">Order #<?= $comment->orderId ?></div>
                            <div class="chips">
                                <?php foreach ($item['categories'] as $index => $cat): ?>
                                    <span class="chip<?= $index === 0 ? ' chip-best' : '' ?>"><?= h($cat->label()) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <div><?= nl2br(h($comment->text)) ?></div>
                            <div class="ship-date"><strong>Expected Ship Date:</strong> <?= $comment->expectedShipDate !== null ? h($comment->expectedShipDate->format('M j, Y')) : '<span class="date-missing">Date Missing</span>' ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>

    <script>
        (function () {
            const toggle = document.getElementById('cat-toggle');
            const panel = document.getElementById('cat-panel');
            const allBox = document.getElementById('cat-all');
            const options = Array.from(document.querySelectorAll('.cat-option'));
            const dateFilter = document.getElementById('date-filter');

            toggle.addEventListener('click', function () {
                panel.hidden = !panel.hidden;
            });

            document.addEventListener('click', function (event) {
                if (!panel.hidden && !panel.contains(event.target) && event.target !== toggle) {
                    panel.hidden = true;
                }
            });

            allBox.addEventListener('change', function () {
                options.forEach(function (option) { option.checked = allBox.checked; });
                apply();
            });

            options.forEach(function (option) {
                option.addEventListener('change', function () {
                    allBox.checked = options.every(function (other) { return other.checked; });
                    apply();
                });
            });

            dateFilter.addEventListener('change', apply);

            function apply() {
                const selected = new Set(
                    options.filter(function (option) { return option.checked; })
                           .map(function (option) { return option.value; })
                );
                const mode = dateFilter.value;

                document.querySelectorAll('section[data-category]').forEach(function (section) {
                    const categoryOn = selected.has(section.dataset.category);
                    let visible = 0;

                    section.querySelectorAll('li[data-has-date]').forEach(function (card) {
                        const hasDate = card.dataset.hasDate === '1';
                        let dateOk;
                        if (mode === 'present') {
                            dateOk = hasDate;
                        } else if (mode === 'missing') {
                            dateOk = !hasDate;
                        } else {
                            dateOk = true;
                        }

                        const show = categoryOn && dateOk;
                        card.style.display = show ? '' : 'none';
                        if (show) { visible += 1; }
                    });

                    section.style.display = (categoryOn && visible > 0) ? '' : 'none';
                    const count = section.querySelector('.count');
                    if (count) { count.textContent = '(' + visible + ')'; }
                });
            }
        })();
    </script>
</body>
</html>
