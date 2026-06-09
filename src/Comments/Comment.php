<?php

declare(strict_types=1);

namespace Sweetwater\Comments;

final class Comment
{
    public function __construct(
        public readonly int $orderId,
        public readonly string $text,
    ) {
    }
}
