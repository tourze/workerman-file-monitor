<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @see      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Tourze\Workerman\FileMonitor;

use Psr\Log\LoggerInterface;
use Tourze\Workerman\FileMonitor\Config\FileMonitorConfig;
use Tourze\Workerman\FileMonitor\Monitor\MemoryMonitor;
use Tourze\Workerman\FileMonitor\Reload\WorkerReloader;
use Tourze\Workerman\FileMonitor\Scanner\FileScanner;
use Workerman\Timer;
use Workerman\Worker;

class FileMonitorWorker
{
    /**
     * 配置管理组件
     */
    private FileMonitorConfig $config;

    /**
     * 文件扫描组件
     */
    private FileScanner $fileScanner;

    /**
     * 内存监控组件
     */
    private MemoryMonitor $memoryMonitor;

    /**
     * Worker 重载组件
     */
    private WorkerReloader $workerReloader;

    /**
     * 构造函数
     */
    public function __construct(
        $monitorDir,
        $monitorExtensions,
        $memoryLimit = null,
        ?LoggerInterface $logger = null
    ) {
        // 初始化配置
        $this->config = new FileMonitorConfig(
            (array) $monitorDir,
            $monitorExtensions,
            $memoryLimit,
            $logger
        );

        // 初始化组件
        $this->fileScanner = new FileScanner($this->config->getMonitorExtensions(), $logger);
        $this->memoryMonitor = new MemoryMonitor($this->config->getParsedMemoryLimit(), $logger);
        $this->workerReloader = new WorkerReloader($logger);

        // 在指定条件下启动监控
        $this->initializeMonitoring();
    }

    /**
     * 初始化监控功能
     */
    private function initializeMonitoring(): void
    {
        // 如果没有 Worker 实例则跳过
        if (!Worker::getAllWorkers()) {
            return;
        }

        // 检查 exec 函数是否可用
        $disableFunctions = explode(',', ini_get('disable_functions'));
        if (in_array('exec', $disableFunctions, true)) {
            $this->config->getLogger()?->error('Monitor file change turned off because exec() has been disabled by disable_functions setting in ' . PHP_CONFIG_FILE_PATH . '/php.ini');
        } else {
            // 非守护进程模式下添加文件监控定时器
            if (!Worker::$daemonize) {
                Timer::add(1, $this->checkAllFilesChange(...));
            }
        }

        // 添加内存监控定时器
        if ($this->memoryMonitor->isSupported()) {
            Timer::add(60, $this->checkMemory(...));
        }
    }

    /**
     * 检查所有目录的文件变化
     */
    public function checkAllFilesChange(): bool
    {
        $hasChanges = $this->fileScanner->checkAllFilesChange($this->config->getMonitorDirectories());

        if ($hasChanges) {
            return $this->workerReloader->reload();
        }

        return false;
    }

    /**
     * 检查内存使用情况
     */
    public function checkMemory(): void
    {
        $this->memoryMonitor->checkMemory();
    }

    /**
     * 获取文件扫描组件
     */
    public function getFileScanner(): FileScanner
    {
        return $this->fileScanner;
    }
    
    /**
     * 设置文件扫描组件（用于测试）
     */
    public function setFileScanner(FileScanner $fileScanner): self
    {
        $this->fileScanner = $fileScanner;
        return $this;
    }
    
    /**
     * 获取内存监控组件
     */
    public function getMemoryMonitor(): MemoryMonitor
    {
        return $this->memoryMonitor;
    }
    
    /**
     * 设置内存监控组件（用于测试）
     */
    public function setMemoryMonitor(MemoryMonitor $memoryMonitor): self
    {
        $this->memoryMonitor = $memoryMonitor;
        return $this;
    }

    /**
     * 获取工作进程重载组件
     */
    public function getWorkerReloader(): WorkerReloader
    {
        return $this->workerReloader;
    }
    
    /**
     * 设置工作进程重载组件（用于测试）
     */
    public function setWorkerReloader(WorkerReloader $workerReloader): self
    {
        $this->workerReloader = $workerReloader;
        return $this;
    }

    /**
     * 获取配置组件
     */
    public function getConfig(): FileMonitorConfig
    {
        return $this->config;
    }
    
    /**
     * 设置配置组件（用于测试）
     */
    public function setConfig(FileMonitorConfig $config): self
    {
        $this->config = $config;
        return $this;
    }
}
