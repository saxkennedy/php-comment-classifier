<?php

declare(strict_types=1);

namespace Sweetwater\Database;

use PDO;
use Sweetwater\Config\Config;

/**
 * Builds the single PDO connection the app uses to talk to MySQL.
 * Connection details come from Config, so the same code works against a local
 * Laragon/XAMPP MySQL and the Dockerised MySQL without changes.
 */
final class Connection
{
    public static function fromConfig(Config $config): PDO
    {
        $host = $config->get('DB_HOST', '127.0.0.1');
        $port = $config->get('DB_PORT', '3306');
        $name = $config->get('DB_NAME', 'sweetwater_test');
        $user = $config->get('DB_USER', 'root');
        $pass = $config->get('DB_PASS', '');

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $host,
            $port,
            $name
        );

        return new PDO($dsn, $user, $pass, [
            // Surface SQL errors as exceptions rather than silent false returns.
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            // Rows come back as associative arrays.
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Use real prepared statements, not driver-side emulation.
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
}
