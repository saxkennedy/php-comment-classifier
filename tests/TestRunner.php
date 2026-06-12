<?php

declare(strict_types=1);

// small test runner (no dependencies)
final class TestRunner
{
    private int $passed = 0;
    private int $failed = 0;

    public function test(string $name, callable $fn): void
    {
        try {
            $fn($this);
            $this->passed++;
            echo "  ok   - {$name}\n";
        } catch (\Throwable $e) {
            $this->failed++;
            echo "  FAIL - {$name}: {$e->getMessage()}\n";
        }
    }

    /**
     * @param mixed $expected
     * @param mixed $actual
     */
    public function assertSame($expected, $actual, string $message = ''): void
    {
        if ($expected !== $actual) {
            throw new \RuntimeException(sprintf(
                '%sexpected %s but got %s',
                $message !== '' ? $message . ': ' : '',
                $this->describe($expected),
                $this->describe($actual)
            ));
        }
    }

    /** @param mixed $actual */
    public function assertNull($actual, string $message = ''): void
    {
        $this->assertSame(null, $actual, $message);
    }

    public function summary(): int
    {
        echo sprintf("\n%d passed, %d failed\n", $this->passed, $this->failed);

        return $this->failed === 0 ? 0 : 1;
    }

    /** @param mixed $value */
    private function describe($value): string
    {
        if (is_array($value)) {
            return '[' . implode(', ', array_map([$this, 'describe'], $value)) . ']';
        }

        return var_export($value, true);
    }
}
