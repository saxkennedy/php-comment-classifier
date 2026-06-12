<?php

declare(strict_types=1);

namespace Sweetwater\Comments;


final class CommentClassifier
{
    /**
     * Case-insensitive substrings that signal each category.
     *
     * @var array<string, list<string>>
     */
    private const RULES = [
        Category::Candy->value => [
            'candy', 'taffy', 'smarties', 'chocolate', 'lollipop', 'gummy',
            'sucker', 'sweet tooth', 'jolly rancher', 'tootsie', 'skittles',
            'm&m', 'licorice', 'peppermint', 'starburst',
        ],
        Category::CallPreference->value => [
            'call me', 'please call', 'give me a call', 'call before',
            'call first', 'call prior', 'call my', 'call ahead', 'call when',
            "don't call", 'do not call', 'dont call', 'no call',
        ],
        Category::Referral->value => [
            'referr', 'referral', 'sales engineer', 'sales rep', 'my sales',
            'my rep', 'recommended by', 'recommended me', 'is my se',
        ],
        Category::Signature->value => [
            'signature', 'sign for', 'sign on delivery', 'require a signature',
            'requires signature', 'require signature', 'no signature',
            'signed for', 'must be signed', 'needs to be signed',
        ],
    ];

    /**
     * Matching categories, best match first. Never empty (Miscellaneous is
     * returned when nothing else matches).
     *
     * @return list<Category>
     */
    public function classify(string $comment): array
    {
        $haystack = strtolower($comment);
        $matched  = [];

        foreach (self::RULES as $category => $needles) {
            $hits = 0;
            foreach ($needles as $needle) {
                if (str_contains($haystack, $needle)) {
                    $hits++;
                }
            }
            if ($hits > 0) {
                $matched[] = ['category' => Category::from($category), 'hits' => $hits];
            }
        }

        if ($matched === []) {
            return [Category::Miscellaneous];
        }

        // Most hits wins best match; ties fall back to the priority order.
        usort($matched, static function (array $a, array $b): int {
            return ($b['hits'] <=> $a['hits'])
                ?: ($a['category']->priority() <=> $b['category']->priority());
        });

        return array_map(static fn (array $m): Category => $m['category'], $matched);
    }
}
