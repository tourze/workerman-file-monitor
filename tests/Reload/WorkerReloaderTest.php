<?php

namespace Tourze\Workerman\FileMonitor\Tests\Reload;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tourze\Workerman\FileMonitor\Reload\WorkerReloader;

class WorkerReloaderTest extends TestCase
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
     * 测试是否支持原生重载 - Unix/Linux
     */
    public function testIsSupportNativeReloadOnUnix()
    {
        $reloader = new WorkerReloader($this->logger);
        
        if (DIRECTORY_SEPARATOR === '/') {
            // Unix/Linux 环境
            $this->assertTrue($reloader->isSupportNativeReload());
        } else {
            // Windows 环境
            $this->assertFalse($reloader->isSupportNativeReload());
        }
    }
    
    /**
     * 测试重载功能 - Windows 环境
     */
    public function testReloadOnWindows()
    {
        if (DIRECTORY_SEPARATOR !== '/') {
            // 仅在 Windows 环境测试
            $this->logger->expects($this->once())
                ->method('info')
                ->with($this->stringContains('File change detected'));
            
            $reloader = new WorkerReloader($this->logger);
            $result = $reloader->reload();
            
            $this->assertTrue($result);
        } else {
            // 在非Windows环境下，测试reload方法的调用
            $reloader = new WorkerReloader($this->logger);
            $result = $reloader->reload();
            // 在Unix/Linux环境下，reload方法应该调用posix_kill
            $this->assertTrue($result);
        }
    }
    
    /**
     * 测试日志记录功能
     */
    public function testLogMessage()
    {
        if (DIRECTORY_SEPARATOR === '/') {
            // Unix/Linux 环境测试日志消息
            $this->logger->expects($this->once())
                ->method('info')
                ->with($this->stringContains('Sending SIGUSR1 signal'));
        } else {
            // Windows 环境测试日志消息
            $this->logger->expects($this->once())
                ->method('info')
                ->with($this->stringContains('File change detected'));
        }
        
        $reloader = new WorkerReloader($this->logger);
        $result = $reloader->reload();
        
        $this->assertTrue($result);
    }
} 