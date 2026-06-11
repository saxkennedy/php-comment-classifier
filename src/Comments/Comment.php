<?php

declare(strict_types=1);

namespace Sweetwater\Comments;

use DateTimeImmutable;

final class Comment
{
    public function __construct(
        public readonly int $orderId,
        public readonly string $text,
        public readonly ?DateTimeImmutable $expectedShipDate = null,
    ) {
    }
}
