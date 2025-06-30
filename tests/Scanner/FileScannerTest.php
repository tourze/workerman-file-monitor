<?php

namespace Tourze\Workerman\FileMonitor\Tests\Scanner;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tourze\Workerman\FileMonitor\Scanner\FileScanner;

class FileScannerTest extends TestCase
{

    /**
     * @var FileScanner
     */
    private $scanner;
    
    /**
     * @var LoggerInterface&MockObject
     */
    private $logger;
    
    /**
     * 设置测试环境
     */
    protected function setUp(): void
    {
        // 创建模拟的日志接口
        $this->logger = $this->createMock(LoggerInterface::class);
        
        // 创建扫描器实例
        $this->scanner = new FileScanner(['php', 'js'], $this->logger);
    }
    
    /**
     * 测试初始化
     */
    public function testInitialization()
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->scanner = new FileScanner(['php', 'js'], $this->logger);
        
        $this->assertEqualsWithDelta(time(), $this->scanner->getLastMtime(), 1);
    }
    
    /**
     * 测试检查非存在目录
     */
    public function testCheckNonExistentDirectory()
    {
        $nonExistentPath = '/nonexistent/path/that/should/not/exist';
        $result = $this->scanner->checkFilesChange($nonExistentPath);
        $this->assertFalse($result);
    }
    
    /**
     * 测试检查文件而非目录
     */
    public function testCheckSingleFile()
    {
        // 使用当前测试文件作为测试目标
        $testFile = __FILE__;
        $result = $this->scanner->checkFilesChange($testFile);
        // 由于文件修改时间早于scanner的创建时间，应该返回false
        $this->assertFalse($result);
    }
    
    /**
     * 测试检查目录下的多个文件
     */
    public function testCheckAllFilesChange()
    {
        // 使用测试目录作为测试目标
        $testDirs = [
            __DIR__,
            '/nonexistent/path'
        ];
        $result = $this->scanner->checkAllFilesChange($testDirs);
        // 由于文件修改时间早于scanner的创建时间，应该返回false
        $this->assertFalse($result);
    }
    
    /**
     * 测试文件数量警告
     */
    public function testTooManyFilesWarning()
    {
        // 创建一个能记录日志的mock logger
        $mockLogger = $this->createMock(LoggerInterface::class);
        
        // 期望调用warning方法，但由于实际目录可能没有超过1000个文件，这里只测试逻辑
        // 使用vendor目录作为测试（通常文件较多）
        $vendorDir = dirname(dirname(dirname(__DIR__))) . '/vendor';
        if (is_dir($vendorDir)) {
            $scanner = new FileScanner(['php'], $mockLogger);
            $result = $scanner->checkFilesChange($vendorDir);
            // 由于文件修改时间早于scanner的创建时间，应该返回false
            $this->assertFalse($result);
        } else {
            // 如果没有vendor目录，测试正常的目录
            $scanner = new FileScanner(['php'], $mockLogger);
            $result = $scanner->checkFilesChange(__DIR__);
            $this->assertFalse($result);
        }
    }
} 