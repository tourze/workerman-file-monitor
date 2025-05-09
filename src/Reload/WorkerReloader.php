<?php

namespace Tourze\Workerman\FileMonitor\Reload;

use Psr\Log\LoggerInterface;

class WorkerReloader
{
    /**
     * 构造函数
     */
    public function __construct(
        private readonly ?LoggerInterface $logger = null
    ) {
    }

    /**
     * 检查是否支持原生 POSIX 信号重新加载
     */
    public function isSupportNativeReload(): bool
    {
        return DIRECTORY_SEPARATOR === '/';
    }

    /**
     * 触发 worker 重新加载
     *
     * 在 Linux/Unix 下使用 SIGUSR1 信号，在 Windows 下返回状态供调用者处理
     */
    public function reload(): bool
    {
        if ($this->isSupportNativeReload()) {
            $this->logger?->info("Sending SIGUSR1 signal to master process for reload");
            posix_kill(posix_getppid(), SIGUSR1);
            return true;
        }

        // Windows 系统无法发送信号，返回状态给调用者处理
        $this->logger?->info("File change detected, reload required");
        return true;
    }
}
