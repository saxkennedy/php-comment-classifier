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
