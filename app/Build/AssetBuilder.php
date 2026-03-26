<?php

declare(strict_types=1);

namespace App\Build;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

final class AssetBuilder
{
    public function __construct(
        private readonly string $projectRoot
    ) {
    }

    public function build(): void
    {
        $resourcesPath = $this->projectRoot . '/resources';
        $outputPath = $this->projectRoot . '/public/assets';

        if (!is_dir($resourcesPath)) {
            throw new RuntimeException('Resources directory not found.');
        }

        $this->ensureDirectory($outputPath . '/css');
        $this->ensureDirectory($outputPath . '/js');
        $this->ensureDirectory($outputPath . '/images');

        $this->writeBundle(
            $resourcesPath . '/css',
            $outputPath . '/css/app.css',
            'css'
        );

        $this->writeBundle(
            $resourcesPath . '/js',
            $outputPath . '/js/app.js',
            'js'
        );

        $this->mirrorDirectory(
            $resourcesPath . '/images',
            $outputPath . '/images'
        );
    }

    private function writeBundle(string $sourceDirectory, string $targetFile, string $extension): void
    {
        $files = $this->collectFiles($sourceDirectory, $extension);
        $bundle = '';

        foreach ($files as $file) {
            $content = trim((string) file_get_contents($file));

            if ($content === '') {
                continue;
            }

            $relativePath = str_replace($this->projectRoot . '/', '', $file);
            $bundle .= '/* Source: ' . $relativePath . ' */' . PHP_EOL;
            $bundle .= $content . PHP_EOL . PHP_EOL;
        }

        file_put_contents($targetFile, $bundle);
    }

    private function mirrorDirectory(string $sourceDirectory, string $targetDirectory): void
    {
        $this->clearDirectory($targetDirectory);

        if (!is_dir($sourceDirectory)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDirectory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = ltrim(str_replace($sourceDirectory, '', $item->getPathname()), '/');
            $targetPath = $targetDirectory . '/' . $relativePath;

            if ($item->isDir()) {
                $this->ensureDirectory($targetPath);
                continue;
            }

            $this->ensureDirectory(dirname($targetPath));
            copy($item->getPathname(), $targetPath);
        }
    }

    /**
     * @return list<string>
     */
    private function collectFiles(string $directory, string $extension): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $item) {
            if (!$item->isFile() || $item->getExtension() !== $extension) {
                continue;
            }

            $files[] = $item->getPathname();
        }

        sort($files);

        return $files;
    }

    private function clearDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            $this->ensureDirectory($directory);

            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
                continue;
            }

            unlink($item->getPathname());
        }
    }

    private function ensureDirectory(string $directory): void
    {
        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create directory: ' . $directory);
        }
    }
}