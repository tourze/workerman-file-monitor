# Workerman File Monitor

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/workerman-file-monitor.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-file-monitor)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/workerman-file-monitor.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-file-monitor)
[![License](https://img.shields.io/github/license/tourze/workerman-file-monitor.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-file-monitor)

A file monitoring service for Workerman projects, extracted from webman framework.

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

## Quick Start

```php
<?php

use Tourze\Workerman\FileMonitor\FileMonitorWorker;
use Workerman\Worker;

// Create your Workerman worker
$worker = new Worker('http://0.0.0.0:8080');

// Initialize file monitor
new FileMonitorWorker(
    monitor_dir: '/path/to/your/code',  // Directory to monitor
    monitor_extensions: ['php'],        // File extensions to monitor
    memory_limit: '512M',               // Optional: memory limit
    logger: $logger                     // Optional: PSR-3 logger
);

Worker::runAll();
```

## Configuration

- `monitor_dir`: Directory or array of directories to monitor
- `monitor_extensions`: Array of file extensions to monitor (e.g., ['php', 'js'])
- `memory_limit`: Memory limit for worker processes (default: 80% of php.ini memory_limit)
- `logger`: PSR-3 compatible logger instance

## Advanced Usage

### Monitoring Multiple Directories

```php
$monitor = new FileMonitorWorker(
    monitor_dir: ['/path/to/dir1', '/path/to/dir2'],
    monitor_extensions: ['php', 'html', 'js']
);
```

### Using Custom Memory Limits

The memory_limit parameter accepts values in the format of '512M', '1G', etc. If not specified, it uses 80% of the value in php.ini.

### Working with Loggers

The monitor works with any PSR-3 compatible logger:

```php
$logger = new YourLogger();

$monitor = new FileMonitorWorker(
    monitor_dir: '/path/to/your/code',
    monitor_extensions: ['php'],
    logger: $logger
);
```

## Notes

- File monitoring is disabled in daemon mode
- Requires `exec()` function to be enabled in PHP
- Memory monitoring is only available on Linux/Unix systems
- Minimum memory limit is 30MB

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
