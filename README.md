# Workerman File Monitor

A file monitoring service for Workerman projects, extracted from webman framework.

[中文文档](#workerman-文件监听服务)

## Features

- Monitor file changes in specified directories
- Support multiple file extensions
- Auto reload when files are modified
- Memory usage monitoring
- PSR-3 logger support
- Cross-platform support (Linux/Unix/Windows)

## Installation

```bash
composer require tourze/workerman-file-monitor
```

## Usage

```php
use Tourze\Workerman\FileMonitor\FileMonitorWorker;

// Create your Workerman worker first
$worker = new Worker('...');

// Initialize file monitor
new FileMonitorWorker(
    monitor_dir: '/path/to/your/code',  // Directory to monitor
    monitor_extensions: ['php'],         // File extensions to monitor
    memory_limit: '512M',               // Optional: memory limit
    logger: $logger                     // Optional: PSR-3 logger
);
```

## Configuration

- `monitor_dir`: Directory or array of directories to monitor
- `monitor_extensions`: Array of file extensions to monitor (e.g., ['php', 'js'])
- `memory_limit`: Memory limit for worker processes (default: 80% of php.ini memory_limit)
- `logger`: PSR-3 compatible logger instance

## Notes

- File monitoring is disabled in daemon mode
- Requires `exec()` function to be enabled in PHP
- Memory monitoring is only available on Linux/Unix systems
- Minimum memory limit is 30MB

---

# Workerman 文件监听服务

从 webman 框架中提取出来的文件监听服务，用于 Workerman 项目。

## 特性

- 监控指定目录的文件变化
- 支持多种文件扩展名
- 文件修改时自动重载
- 内存使用监控
- 支持 PSR-3 日志记录
- 跨平台支持（Linux/Unix/Windows）

## 安装

```bash
composer require tourze/workerman-file-monitor
```

## 使用方法

```php
use Tourze\Workerman\FileMonitor\FileMonitorWorker;

// 先创建你的 Workerman worker
$worker = new Worker('...');

// 初始化文件监听器
new FileMonitorWorker(
    monitor_dir: '/path/to/your/code',  // 要监控的目录
    monitor_extensions: ['php'],         // 要监控的文件扩展名
    memory_limit: '512M',               // 可选：内存限制
    logger: $logger                     // 可选：PSR-3 日志记录器
);
```

## 配置说明

- `monitor_dir`: 要监控的目录或目录数组
- `monitor_extensions`: 要监控的文件扩展名数组（如 ['php', 'js']）
- `memory_limit`: worker 进程的内存限制（默认：php.ini 中 memory_limit 的 80%）
- `logger`: PSR-3 兼容的日志记录器实例

## 注意事项

- 守护进程模式下文件监控会被禁用
- 需要 PHP 中启用 `exec()` 函数
- 内存监控仅在 Linux/Unix 系统上可用
- 最小内存限制为 30MB
