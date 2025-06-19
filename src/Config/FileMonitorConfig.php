<?php

namespace Tourze\Workerman\FileMonitor\Config;

use Psr\Log\LoggerInterface;

class FileMonitorConfig
{
    /**
     * 构造函数
     */
    public function __construct(
        private readonly array $monitorDirectories,
        private readonly array $monitorExtensions,
        private readonly ?string $memoryLimit = null,
        private readonly ?LoggerInterface $logger = null
    ) {
    }

    /**
     * 获取监控目录
     */
    public function getMonitorDirectories(): array
    {
        return $this->monitorDirectories;
    }

    /**
     * 获取监控文件扩展名列表
     */
    public function getMonitorExtensions(): array
    {
        return $this->monitorExtensions;
    }

    /**
     * 获取内存限制
     */
    public function getMemoryLimit(): ?string
    {
        return $this->memoryLimit;
    }

    /**
     * 获取解析后的内存限制（MB）
     */
    public function getParsedMemoryLimit(): int
    {
        return $this->parseMemoryLimit($this->memoryLimit);
    }

    /**
     * 获取日志记录器
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    /**
     * 解析内存限制值为 MB 整数
     */
    private function parseMemoryLimit(?string $memoryLimit): int
    {
        if ($memoryLimit === '0') {
            return 0;
        }

        $usePhpIni = false;
        if (empty($memoryLimit)) {
            $memoryLimit = ini_get('memory_limit');
            $usePhpIni = true;
        }

        if (-1 == $memoryLimit || $memoryLimit === '-1') {
            return 0;
        }

        $unit = $memoryLimit[strlen($memoryLimit) - 1];
        if ('G' === $unit) {
            $memoryLimit = 1024 * (int) $memoryLimit;
        } elseif ('M' === $unit) {
            $memoryLimit = (int) $memoryLimit;
        } elseif ('K' === $unit) {
            $memoryLimit = (int) ($memoryLimit) / 1024;
            // 如果 KB 值太小，可能会被下面的最小限制覆盖
        } else {
            // 纯数字，假设为字节
            $memoryLimit = (int) (intval($memoryLimit) / (1024 * 1024));
        }

        // 应用最小限制
        if ($memoryLimit < 30) {
            $memoryLimit = 30;
        }

        if ($usePhpIni) {
            $memoryLimit = (int) (0.8 * $memoryLimit);
        }

        return (int) $memoryLimit;
    }
}
