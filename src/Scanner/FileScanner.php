<?php

namespace Tourze\Workerman\FileMonitor\Scanner;

use FilesystemIterator;
use Psr\Log\LoggerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class FileScanner
{
    /**
     * 上次扫描时间
     */
    private int $lastMtime;

    /**
     * 过多文件检查标记
     */
    private bool $tooManyFilesCheck = false;

    /**
     * 构造函数
     */
    public function __construct(
        private readonly array $monitorExtensions,
        private readonly ?LoggerInterface $logger = null
    ) {
        $this->lastMtime = time();
    }

    /**
     * 检查一个目录下的文件变更
     */
    public function checkFilesChange(string $monitorDir): bool
    {
        clearstatcache();

        // 检查目录或文件是否存在
        if (!is_dir($monitorDir)) {
            if (!is_file($monitorDir)) {
                return false;
            }
            $iterator = [new SplFileInfo($monitorDir)];
        } else {
            // 递归遍历目录
            $dirIterator = new RecursiveDirectoryIterator(
                $monitorDir,
                FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
            );
            $iterator = new RecursiveIteratorIterator($dirIterator);
        }

        $count = 0;
        foreach ($iterator as $file) {
            ++$count;
            /** @var SplFileInfo $file */
            if (is_dir($file->getRealPath())) {
                continue;
            }

            // 检查文件修改时间
            if ($this->lastMtime < $file->getMTime()
                && in_array($file->getExtension(), $this->monitorExtensions, true)
            ) {
                // 检查PHP语法错误
                $var = 0;
                exec('"' . PHP_BINARY . '" -l ' . $file, $out, $var);
                if ($var) {
                    $this->lastMtime = $file->getMTime();
                    continue;
                }

                $this->lastMtime = $file->getMTime();
                $this->logger?->info("{$file} update and reload");

                return true;
            }
        }

        // 检查文件数量过多警告
        if (!$this->tooManyFilesCheck && $count > 1000) {
            $this->logger?->warning("Monitor: There are too many files ($count files) in $monitorDir which makes file monitoring very slow");
            $this->tooManyFilesCheck = true;
        }

        return false;
    }

    /**
     * 检查多个目录下的文件变更
     */
    public function checkAllFilesChange(array $monitorDirs): bool
    {
        foreach ($monitorDirs as $path) {
            if ($this->checkFilesChange($path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 获取上次修改时间
     */
    public function getLastMtime(): int
    {
        return $this->lastMtime;
    }
}
