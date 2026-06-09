<?php

declare(strict_types=1);

namespace Sweetwater\Config;

/**
 * Resolve config in this order:
 *   1. real environment variables  (Docker)
 *   2. an optional .env file        (locals, not committed)
 *   3. defaults    (stock Laragon / XAMPP values)
 */
final class Config
{
    /** @var array<string, string> values parsed from the .env file */
    private array $fileValues;

    /** @param array<string, string> $fileValues */
    private function __construct(array $fileValues)
    {
        $this->fileValues = $fileValues;
    }

    public static function load(string $projectRoot): self
    {
        return new self(self::parseEnvFile($projectRoot . '/.env'));
    }

    public function get(string $key, string $default = ''): string
    {
        $fromEnv = getenv($key);
        if ($fromEnv !== false && $fromEnv !== '') {
            return $fromEnv;
        }

        if (isset($this->fileValues[$key]) && $this->fileValues[$key] !== '') {
            return $this->fileValues[$key];
        }

        return $default;
    }

    /**
     * Parse simple .env
     *
     * @return array<string, string>
     */
    private static function parseEnvFile(string $path): array
    {
        if (!is_file($path)) {
            return [];
        }

        $values = [];
        $lines  = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
                $value = trim($value, "\"'");
            }

            if ($key !== '') {
                $values[$key] = $value;
            }
        }

        return $values;
    }
}
