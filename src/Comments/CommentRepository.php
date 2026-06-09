<?php

declare(strict_types=1);

namespace Sweetwater\Comments;

use PDO;

final class CommentRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * All comments, ordered by order id for a stable, repeatable report.
     *
     * @return list<Comment>
     */
    public function all(): array
    {
        $rows = $this->pdo
            ->query('SELECT orderid, comments FROM sweetwater_test ORDER BY orderid')
            ->fetchAll();

        return array_map(
            static fn (array $row): Comment => new Comment(
                (int) $row['orderid'],
                (string) $row['comments'],
            ),
            $rows,
        );
    }
}
