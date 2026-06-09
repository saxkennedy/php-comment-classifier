<?php

declare(strict_types=1);

/**
 * Minimal PSR-4 autoloader for the Sweetwater\ namespace.
 *
 * The brief asks for our own code rather than third-party packages, so I map
 * class names onto files here instead of pulling in something like Composer.
 */
spl_autoload_register(static function (string $class): void {
    $prefix  = 'Sweetwater\\';
    $baseDir = __DIR__ . '/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file     = $baseDir . str_replace('\\', '/', $relative) . '.php';

    if (is_file($file)) {
        require $file;
    }
});
