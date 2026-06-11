<?php

declare(strict_types=1);

namespace Sweetwater\Comments;

use DateTimeImmutable;
use PDO;

final class CommentRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
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

    public function updateShipDate(int $orderId, DateTimeImmutable $date): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE sweetwater_test SET shipdate_expected = :date WHERE orderid = :id'
        );
        $stmt->execute([
            ':date' => $date->format('Y-m-d H:i:s'),
            ':id'   => $orderId,
        ]);
    }
}
