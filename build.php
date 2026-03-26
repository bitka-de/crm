<?php

declare(strict_types=1);

use App\Build\AssetBuilder;

require_once __DIR__ . '/vendor/autoload.php';

$command = $argv[1] ?? null;

if ($command !== 'build') {
    fwrite(STDERR, "Usage: php build.php build\n");
    exit(1);
}

$builder = new AssetBuilder(__DIR__);
$builder->build();

fwrite(STDOUT, "Assets built successfully.\n");