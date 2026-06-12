<?php

declare(strict_types=1);

namespace Sweetwater\Comments;

enum Category: string
{
    case Candy = 'candy';
    case CallPreference = 'call';
    case Referral = 'referral';
    case Signature = 'signature';
    case Miscellaneous = 'misc';

    // Full name for section headings and filter options.
    public function heading(): string
    {
        return match ($this) {
            self::Candy          => 'Candy',
            self::CallPreference => "Do/Don't Call",
            self::Referral       => 'Referral',
            self::Signature      => 'Signature Requirements',
            self::Miscellaneous  => 'Miscellaneous',
        };
    }

    // Short name for the tags shown on each comment.
    public function label(): string
    {
        return match ($this) {
            self::Candy          => 'Candy',
            self::CallPreference => "Do/Don't Call",
            self::Referral       => 'Referral',
            self::Signature      => 'Signature',
            self::Miscellaneous  => 'Misc.',
        };
    }

    // Tie-break order for best match when categories have equal hit counts.
    public function priority(): int
    {
        return match ($this) {
            self::CallPreference => 1,
            self::Signature      => 2,
            self::Referral       => 3,
            self::Candy          => 4,
            self::Miscellaneous  => 5,
        };
    }

    /**
     * Sections in priority order (Miscellaneous lands last via its priority).
     *
     * @return list<self>
     */
    public static function displayOrder(): array
    {
        $cases = self::cases();
        usort($cases, static fn (self $a, self $b): int => $a->priority() <=> $b->priority());

        return $cases;
    }
}
