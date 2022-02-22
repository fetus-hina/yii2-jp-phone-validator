#!/usr/bin/env php
<?php

declare(strict_types=1);

define('BASE_DIR', dirname(__DIR__));
define('PATCH_DIR', BASE_DIR . '/data/ci-patch');

if (!$patchPaths = getPatchFiles()) {
    fwrite(STDERR, "No patches found.\n");
    exit(0);
}

foreach ($patchPaths as $path) {
    if (!applyPatch($path)) {
        exit(1);
    }
}
exit(0);

function getPatchFiles(): array
{
    $it = new CallbackFilterIterator(
        new FilesystemIterator(
            PATCH_DIR,
            FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_PATHNAME
        ),
        function (SplFileInfo $fileInfo): bool {
            if (
                $fileInfo->isDir() ||
                !$fileInfo->isReadable() ||
                !preg_match('/^php-([0-9.]+).*?\.patch$/', $fileInfo->getBasename(), $match)
            ) {
                return false;
            }

            return version_compare(PHP_VERSION, $match[1], '>=');
        }
    );
    $results = array_map(
        function (SplFileInfo $fileInfo): string {
            return $fileInfo->getPathname();
        },
        iterator_to_array($it)
    );
    natcasesort($results);
    return array_values($results);
}

function applyPatch(string $path): bool
{
    fwrite(STDERR, 'Applying patch ' . basename($path) . "\n");

    if (!$pwd = getcwd()) {
        fwrite(STDERR, "Failed to get current working directory.\n");
        return false;
    }

    if (!chdir(BASE_DIR)) {
        fwrite(STDERR, "Failed to change working directory to " . BASE_DIR . "\n");
        return false;
    }

    try {
        $cmdline = vsprintf('cat %s | patch -p1', [
            escapeshellarg($path),
        ]);
        passthru($cmdline, $status);
        return $status === 0;
    } finally {
        chdir($pwd);
    }
}
