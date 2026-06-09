<?php

declare(strict_types=1);

namespace Sweetwater\Comments;

/**
 * Assigns a comment to one or more report categories using explicit phrase
 * rules that catch likely phrases given the actual comment data.
 *
 * A comment can match several categories. One that asks for candy and to be
 * called belongs in both. Anything that matches no named category falls
 * through to Miscellaneous.
 *
 * The phrase lists were built by reading the actual comment data and matching
 * the wording customers really used. Subtle, ambiguous mentions (a bare name
 * drop with no "referred"/"sales rep" cue) are intentionally left to
 * Miscellaneous rather than risk false positives.
 */
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
     * Every category the comment belongs to (never empty; Miscellaneous is
     * returned when nothing else matches).
     *
     * @return list<Category>
     */
    public function classify(string $comment): array
    {
        $haystack = strtolower($comment);
        $matched  = [];

        foreach (self::RULES as $category => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($haystack, $needle)) {
                    $matched[] = Category::from($category);
                    break;
                }
            }
        }

        return $matched === [] ? [Category::Miscellaneous] : $matched;
    }
}
