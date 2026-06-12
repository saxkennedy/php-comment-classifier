<?php

declare(strict_types=1);

use Sweetwater\Comments\Category;
use Sweetwater\Comments\CommentClassifier;

/** @var TestRunner $runner */
echo "\nCommentClassifier\n";

$classifier = new CommentClassifier();

// Classify and return the category values, best match first.
$values = static function (string $text) use ($classifier): array {
    return array_map(static fn (Category $c): string => $c->value, $classifier->classify($text));
};

$runner->test('classifies a single clear category', function (TestRunner $t) use ($values) {
    $t->assertSame(['candy'], $values('More banana taffy please :)'));
});

$runner->test('falls back to miscellaneous when nothing matches', function (TestRunner $t) use ($values) {
    $t->assertSame(['misc'], $values('Thanks guys, have a great day!'));
});

$runner->test('never appends miscellaneous to a named match', function (TestRunner $t) use ($values) {
    $t->assertSame(['candy'], $values('Could you add some candy?'));
});

$runner->test('orders multiple matches by hit count (most hits is best)', function (TestRunner $t) use ($values) {
    // three candy words vs one call phrase -> candy wins
    $t->assertSame(['candy', 'call'], $values('smarties, taffy, and chocolate, call me'));
});

$runner->test('breaks ties by priority (call over candy)', function (TestRunner $t) use ($values) {
    // one candy word, one call phrase -> tie -> priority puts call first
    $t->assertSame(['call', 'candy'], $values('smarties, call me'));
});

$runner->test('ranks three categories best first', function (TestRunner $t) use ($values) {
    // candy, call, signature each match once -> priority order call, signature, candy
    $t->assertSame(['call', 'signature', 'candy'], $values('candy, call me, signature required'));
});

$runner->test('always returns at least one category', function (TestRunner $t) use ($classifier) {
    $t->assertSame(1, count($classifier->classify('')));
});
