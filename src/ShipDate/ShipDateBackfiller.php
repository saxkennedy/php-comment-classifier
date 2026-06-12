<?php

declare(strict_types=1);

namespace Sweetwater\ShipDate;

use Sweetwater\Comments\CommentRepository;

// Fills shipdate_expected from the comment text. It only touches rows that
// still have no date, so it is idempotent.
final class ShipDateBackfiller
{
    public function __construct(
        private readonly CommentRepository $repo,
        private readonly ShipDateParser $parser,
    ) {
    }

    public function run(): int
    {
        $filled = 0;

        foreach ($this->repo->all() as $comment) {
            if ($comment->expectedShipDate !== null) {
                continue;
            }

            $date = $this->parser->parse($comment->text);
            if ($date === null) {
                continue;
            }

            $this->repo->updateShipDate($comment->orderId, $date);
            $filled++;
        }

        return $filled;
    }
}
