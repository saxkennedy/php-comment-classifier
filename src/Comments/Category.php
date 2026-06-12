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

    public function heading(): string
    {
        return match ($this) {
            self::Candy          => 'Comments about candy',
            self::CallPreference => "Comments about call me / don't call me",
            self::Referral       => 'Comments about who referred me',
            self::Signature      => 'Comments about signature requirements upon delivery',
            self::Miscellaneous  => 'Miscellaneous comments',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Candy          => 'Candy',
            self::CallPreference => "Call / don't call",
            self::Referral       => 'Referral',
            self::Signature      => 'Signature',
            self::Miscellaneous  => 'Miscellaneous',
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
     * Sections in display order, with miscellaneous last as the catch-all.
     *
     * @return list<self>
     */
    public static function displayOrder(): array
    {
        return [
            self::Candy,
            self::CallPreference,
            self::Referral,
            self::Signature,
            self::Miscellaneous,
        ];
    }
}
