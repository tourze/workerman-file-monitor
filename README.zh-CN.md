# Workerman 文件监听服务

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/workerman-file-monitor.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-file-monitor)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/workerman-file-monitor.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-file-monitor)
[![License](https://img.shields.io/github/license/tourze/workerman-file-monitor.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-file-monitor)

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

## 快速开始

```php
<?php

use Tourze\Workerman\FileMonitor\FileMonitorWorker;
use Workerman\Worker;

// 创建你的 Workerman worker
$worker = new Worker('http://0.0.0.0:8080');

// 初始化文件监听器
new FileMonitorWorker(
    monitor_dir: '/path/to/your/code',  // 要监控的目录
    monitor_extensions: ['php'],        // 要监控的文件扩展名
    memory_limit: '512M',               // 可选：内存限制
    logger: $logger                     // 可选：PSR-3 日志记录器
);

Worker::runAll();
```

## 配置说明

- `monitor_dir`: 要监控的目录或目录数组
- `monitor_extensions`: 要监控的文件扩展名数组（如 ['php', 'js']）
- `memory_limit`: worker 进程的内存限制（默认：php.ini 中 memory_limit 的 80%）
- `logger`: PSR-3 兼容的日志记录器实例

## 高级用法

### 监控多个目录

```php
$monitor = new FileMonitorWorker(
    monitor_dir: ['/path/to/dir1', '/path/to/dir2'],
    monitor_extensions: ['php', 'html', 'js']
);
```

### 使用自定义内存限制

memory_limit 参数接受 '512M'、'1G' 等格式的值。如果未指定，则使用 php.ini 中值的 80%。

### 使用日志记录器

监控器可以与任何 PSR-3 兼容的日志记录器一起使用：

```php
$logger = new YourLogger();

$monitor = new FileMonitorWorker(
    monitor_dir: '/path/to/your/code',
    monitor_extensions: ['php'],
    logger: $logger
);
```

## 注意事项

- 守护进程模式下文件监控会被禁用
- 需要 PHP 中启用 `exec()` 函数
- 内存监控仅在 Linux/Unix 系统上可用
- 最小内存限制为 30MB

## 贡献指南

请查看 [CONTRIBUTING.md](CONTRIBUTING.md) 了解详情。

## 许可证

本项目基于 MIT 许可证发布。详情请查看 [许可证文件](LICENSE)。
