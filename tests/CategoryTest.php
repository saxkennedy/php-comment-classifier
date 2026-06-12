<?php

declare(strict_types=1);

use Sweetwater\Comments\Category;

/** @var TestRunner $runner */
echo "\nCategory\n";

$runner->test('displayOrder follows the best-match priority', function (TestRunner $t) {
    $order = array_map(static fn (Category $c): string => $c->value, Category::displayOrder());
    $t->assertSame(['call', 'signature', 'referral', 'candy', 'misc'], $order);
});

$runner->test('miscellaneous sorts last', function (TestRunner $t) {
    $order = Category::displayOrder();
    $t->assertSame(Category::Miscellaneous, $order[array_key_last($order)]);
});
