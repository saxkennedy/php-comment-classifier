<?php

declare(strict_types=1);

use Sweetwater\ShipDate\ShipDateParser;

/** @var TestRunner $runner */
echo "ShipDateParser\n";

$parser = new ShipDateParser();

$runner->test('parses the labeled MM/DD/YY date', function (TestRunner $t) use ($parser) {
    $date = $parser->parse("Norb rocks!\nExpected Ship Date: 01/07/18\n");
    $t->assertSame('2018-01-07', $date?->format('Y-m-d'));
});

$runner->test('accepts single-digit month and day', function (TestRunner $t) use ($parser) {
    $t->assertSame('2018-01-07', $parser->parse('Expected Ship Date: 1/7/18')?->format('Y-m-d'));
});

$runner->test('respects a four-digit year', function (TestRunner $t) use ($parser) {
    $t->assertSame('2018-07-04', $parser->parse('Expected Ship Date: 7/4/2018')?->format('Y-m-d'));
});

$runner->test('is case-insensitive on the label', function (TestRunner $t) use ($parser) {
    $t->assertSame('2018-03-15', $parser->parse('expected ship date: 03/15/18')?->format('Y-m-d'));
});

$runner->test('returns null when there is no label', function (TestRunner $t) use ($parser) {
    $t->assertNull($parser->parse('Thanks, please leave the package at the door.'));
});

$runner->test('ignores an unrelated date in the body (9/11 memorial)', function (TestRunner $t) use ($parser) {
    $t->assertNull($parser->parse('Singing at the JERSEY CITY 9/11/2018 Memorial. You guys are great!'));
});

$runner->test('ignores a pickup date that is not the ship-date label', function (TestRunner $t) use ($parser) {
    $t->assertNull($parser->parse("Pick up 9/5/18\nLocal Pickup: contact the customer."));
});

$runner->test('returns null for an impossible calendar date', function (TestRunner $t) use ($parser) {
    $t->assertNull($parser->parse('Expected Ship Date: 13/40/18'));
});

$runner->test('returns null when the label has no readable date', function (TestRunner $t) use ($parser) {
    $t->assertNull($parser->parse('Expected Ship Date: TBD'));
});
