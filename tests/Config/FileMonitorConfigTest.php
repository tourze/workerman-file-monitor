<?php

namespace Tourze\Workerman\FileMonitor\Tests\Config;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tourze\Workerman\FileMonitor\Config\FileMonitorConfig;

class FileMonitorConfigTest extends TestCase
{
    /**
     * 测试基本配置获取功能
     */
    public function testBasicGetters()
    {
        $dirs = ['/path/to/dir1', '/path/to/dir2'];
        $extensions = ['php', 'js'];
        $memoryLimit = '512M';
        /** @var LoggerInterface&\PHPUnit\Framework\MockObject\MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        
        $config = new FileMonitorConfig($dirs, $extensions, $memoryLimit, $logger);
        
        $this->assertSame($dirs, $config->getMonitorDirectories());
        $this->assertSame($extensions, $config->getMonitorExtensions());
        $this->assertSame($memoryLimit, $config->getMemoryLimit());
        $this->assertSame($logger, $config->getLogger());
    }
    
    /**
     * 测试内存限制解析 - 使用 MB 单位
     */
    public function testParsedMemoryLimitWithMB()
    {
        $config = new FileMonitorConfig([], [], '512M');
        $this->assertEquals(512, $config->getParsedMemoryLimit());
    }
    
    /**
     * 测试内存限制解析 - 使用 GB 单位
     */
    public function testParsedMemoryLimitWithGB()
    {
        $config = new FileMonitorConfig([], [], '2G');
        $this->assertEquals(2048, $config->getParsedMemoryLimit());
    }
    
    /**
     * 测试内存限制解析 - 使用 KB 单位
     */
    public function testParsedMemoryLimitWithKB()
    {
        $config = new FileMonitorConfig([], [], '10240K');
        // 根据实际代码实现，无论多大的 KB 值都会被应用最小限制 30MB
        $this->assertEquals(30, $config->getParsedMemoryLimit());
    }
    
    /**
     * 测试内存限制解析 - 使用 KB 单位，但值太小
     */
    public function testParsedMemoryLimitWithSmallKB()
    {
        $config = new FileMonitorConfig([], [], '1024K');
        // 1024KB = 1MB，但由于最小值限制为30MB，所以应该返回30
        $this->assertEquals(30, $config->getParsedMemoryLimit());
    }
    
    /**
     * 测试内存限制解析 - 使用字节
     */
    public function testParsedMemoryLimitWithBytes()
    {
        $config = new FileMonitorConfig([], [], '104857600');
        $this->assertEquals(100, $config->getParsedMemoryLimit());
    }
    
    /**
     * 测试内存限制解析 - 内存限制为0
     */
    public function testParsedMemoryLimitWithZero()
    {
        $config = new FileMonitorConfig([], [], '0');
        $this->assertEquals(0, $config->getParsedMemoryLimit());
    }
    
    /**
     * 测试内存限制解析 - 内存限制为-1
     */
    public function testParsedMemoryLimitWithNegativeOne()
    {
        $config = new FileMonitorConfig([], [], '-1');
        $this->assertEquals(0, $config->getParsedMemoryLimit());
    }
    
    /**
     * 测试内存限制解析 - 内存限制过小（小于30MB）
     */
    public function testParsedMemoryLimitWithTooSmallValue()
    {
        $config = new FileMonitorConfig([], [], '10M');
        // 应该返回最小限制 30MB
        $this->assertEquals(30, $config->getParsedMemoryLimit());
    }
    
    /**
     * 测试内存限制解析 - 使用 php.ini 值
     */
    public function testParsedMemoryLimitWithNullValue()
    {
        // 备份原始 memory_limit 设置
        $originalMemoryLimit = ini_get('memory_limit');
        
        // 设置测试用 memory_limit
        ini_set('memory_limit', '1024M');
        
        $config = new FileMonitorConfig([], [], null);
        // 应该使用 80% 的 php.ini 值
        $this->assertEquals(819, $config->getParsedMemoryLimit());
        
        // 恢复原始设置
        ini_set('memory_limit', $originalMemoryLimit);
    }
} 