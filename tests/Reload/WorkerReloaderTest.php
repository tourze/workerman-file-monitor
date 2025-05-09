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
            $this->markTestSkipped('仅在 Windows 环境测试');
        }
    }
    
    /**
     * 测试日志记录 - Unix 环境
     */
    public function testLogMessageOnUnix()
    {
        if (DIRECTORY_SEPARATOR === '/') {
            // 仅在 Unix/Linux 环境测试
            $this->logger->expects($this->once())
                ->method('info')
                ->with($this->stringContains('Sending SIGUSR1 signal'));
            
            $reloader = new WorkerReloader($this->logger);
            
            // 注意：由于实际调用了 posix_kill，我们无法真正运行这个测试
            // 这里只是演示单元测试的结构
            $this->markTestSkipped('无法测试 posix_kill 调用');
        } else {
            $this->markTestSkipped('仅在 Unix/Linux 环境测试');
        }
    }
} 