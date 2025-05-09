<?php

namespace Tourze\Workerman\FileMonitor\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tourze\Workerman\FileMonitor\Config\FileMonitorConfig;
use Tourze\Workerman\FileMonitor\FileMonitorWorker;
use Tourze\Workerman\FileMonitor\Monitor\MemoryMonitor;
use Tourze\Workerman\FileMonitor\Reload\WorkerReloader;
use Tourze\Workerman\FileMonitor\Scanner\FileScanner;

class FileMonitorWorkerTest extends TestCase
{
    /**
     * @var LoggerInterface&MockObject
     */
    private $logger;
    
    /**
     * 设置测试环境
     */
    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
    }
    
    /**
     * 测试初始化 - 验证组件是否正确创建
     */
    public function testInitialization()
    {
        $monitorDir = ['/path/to/dir'];
        $monitorExtensions = ['php', 'js'];
        $memoryLimit = '256M';
        
        $worker = new FileMonitorWorker($monitorDir, $monitorExtensions, $memoryLimit, $this->logger);
        
        // 验证组件正确创建
        $this->assertInstanceOf(FileMonitorConfig::class, $worker->getConfig());
        $this->assertInstanceOf(FileScanner::class, $worker->getFileScanner());
        $this->assertInstanceOf(MemoryMonitor::class, $worker->getMemoryMonitor());
        $this->assertInstanceOf(WorkerReloader::class, $worker->getWorkerReloader());
        
        // 验证配置值正确传递
        $config = $worker->getConfig();
        $this->assertSame($monitorDir, $config->getMonitorDirectories());
        $this->assertSame($monitorExtensions, $config->getMonitorExtensions());
        $this->assertSame($memoryLimit, $config->getMemoryLimit());
        $this->assertSame($this->logger, $config->getLogger());
    }
    
    /**
     * 测试文件变化检查 - 无变化
     */
    public function testCheckAllFilesChangeWithNoChanges()
    {
        $monitorDir = ['/path/to/dir'];
        $monitorExtensions = ['php'];
        
        // 创建模拟对象
        /** @var FileScanner&MockObject $scannerMock */
        $scannerMock = $this->createMock(FileScanner::class);
        $scannerMock->method('checkAllFilesChange')
            ->with($monitorDir)
            ->willReturn(false);
        
        // 测试实例
        $worker = new FileMonitorWorker($monitorDir, $monitorExtensions, null, $this->logger);
        
        // 使用 setter 方法替换内部 FileScanner
        $worker->setFileScanner($scannerMock);
        
        // 执行测试
        $result = $worker->checkAllFilesChange();
        $this->assertFalse($result);
    }
    
    /**
     * 测试文件变化检查 - 有变化
     */
    public function testCheckAllFilesChangeWithChanges()
    {
        $monitorDir = ['/path/to/dir'];
        $monitorExtensions = ['php'];
        
        // 创建模拟对象
        /** @var FileScanner&MockObject $scannerMock */
        $scannerMock = $this->createMock(FileScanner::class);
        $scannerMock->method('checkAllFilesChange')
            ->with($monitorDir)
            ->willReturn(true);
        
        /** @var WorkerReloader&MockObject $reloaderMock */
        $reloaderMock = $this->createMock(WorkerReloader::class);
        $reloaderMock->method('reload')
            ->willReturn(true);
        
        // 测试实例
        $worker = new FileMonitorWorker($monitorDir, $monitorExtensions, null, $this->logger);
        
        // 使用 setter 方法替换内部组件
        $worker->setFileScanner($scannerMock);
        $worker->setWorkerReloader($reloaderMock);
        
        // 执行测试
        $result = $worker->checkAllFilesChange();
        $this->assertTrue($result);
    }
    
    /**
     * 测试内存检测
     */
    public function testCheckMemory()
    {
        $worker = new FileMonitorWorker(['/path/to/dir'], ['php'], null, $this->logger);
        
        // 创建模拟对象
        /** @var MemoryMonitor&MockObject $monitorMock */
        $monitorMock = $this->createMock(MemoryMonitor::class);
        $monitorMock->expects($this->once())
            ->method('checkMemory');
        
        // 使用 setter 方法替换内部组件
        $worker->setMemoryMonitor($monitorMock);
        
        // 执行测试
        $worker->checkMemory();
    }
} 