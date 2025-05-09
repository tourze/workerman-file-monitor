<?php

namespace Tourze\Workerman\FileMonitor\Tests\Monitor;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tourze\Workerman\FileMonitor\Monitor\MemoryMonitor;

class MemoryMonitorTest extends TestCase
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
     * 测试是否支持内存监控 - 内存限制为 0
     */
    public function testIsSupportedWithZeroMemoryLimit()
    {
        $monitor = new MemoryMonitor(0, $this->logger);
        $this->assertFalse($monitor->isSupported());
    }
    
    /**
     * 测试是否支持内存监控 - 有效内存限制
     */
    public function testIsSupportedWithValidMemoryLimit()
    {
        $originalDirectory = DIRECTORY_SEPARATOR;
        
        // 模拟 Windows 环境
        if ($originalDirectory === '/') {
            // 当在 Unix/Linux 环境中测试
            $monitor = new MemoryMonitor(100, $this->logger);
            $this->assertTrue($monitor->isSupported());
        } else {
            // 当在 Windows 环境中测试
            // 在 Windows 中目录分隔符不是 '/'，所以应该返回 false
            $monitor = new MemoryMonitor(100, $this->logger);
            $this->assertFalse($monitor->isSupported());
        }
    }
    
    /**
     * 测试检查内存功能 - 在不支持时应当直接返回
     */
    public function testCheckMemoryReturnsEarlyWhenNotSupported()
    {
        // Windows 环境或内存限制为 0 时，应当早返回
        $monitor = new MemoryMonitor(0, $this->logger);
        
        // 由于实现中没有依赖注入， 这里只能简单验证方法调用不会抛出异常
        $this->assertNull($monitor->checkMemory());
    }
} 