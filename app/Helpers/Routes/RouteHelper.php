<?php

declare(strict_types=1);

namespace App\Helpers\Routes;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class RouteHelper
{
    public static function includeRouteFiles(string $directory): void
    {
        $directoryIterator = new RecursiveDirectoryIterator(
            directory: $directory,
        );

        /** @var RecursiveDirectoryIterator|RecursiveIteratorIterator $iterator */
        $iterator = new RecursiveIteratorIterator(
            iterator: $directoryIterator,
        );

        while ($iterator->valid()) {
            if (
                !$iterator->isDot()
                && $iterator->isFile()
                && $iterator->isReadable()
                && $iterator->current()->getExtension() === 'php'
            ) {
                require $iterator->key();
            }

            $iterator->next();
        }
    }
}
