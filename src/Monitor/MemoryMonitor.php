<?php

namespace Tourze\Workerman\FileMonitor\Monitor;

use Psr\Log\LoggerInterface;

class MemoryMonitor
{
    /**
     * 构造函数
     */
    public function __construct(
        private readonly int $memoryLimit = 0,
        private readonly ?LoggerInterface $logger = null
    ) {
    }

    /**
     * 检查是否支持内存监控
     */
    public function isSupported(): bool
    {
        return $this->memoryLimit > 0 && DIRECTORY_SEPARATOR === '/';
    }

    /**
     * 检查进程内存使用情况
     */
    public function checkMemory(): void
    {
        // 不支持则跳过
        if (!$this->isSupported()) {
            return;
        }

        $ppid = posix_getppid();
        $childrenFile = "/proc/$ppid/task/$ppid/children";

        if (!is_file($childrenFile) || !($children = file_get_contents($childrenFile))) {
            return;
        }

        foreach (explode(' ', $children) as $pid) {
            $pid = (int) $pid;
            $statusFile = "/proc/$pid/status";

            if (!is_file($statusFile) || !($status = file_get_contents($statusFile))) {
                continue;
            }

            $mem = 0;
            if (preg_match('/VmRSS\s*?:\s*?(\d+?)\s*?kB/', $status, $match)) {
                $mem = $match[1];
            }

            $mem = (int) ($mem / 1024);
            if ($mem >= $this->memoryLimit) {
                posix_kill($pid, SIGINT);
                $this->logger?->warning("Process $pid exceeded memory limit ($mem MB >= {$this->memoryLimit} MB), sending SIGINT signal");
            }
        }
    }
}
