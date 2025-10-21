<?php

/**
 * Stubs for Swoole classes and functions
 * These are only used for static analysis and don't affect runtime
 */

namespace Swoole;

if (!class_exists('Swoole\Runtime', false)) {
    class Runtime
    {
        public static function enableCoroutine(int $flags): void {}
    }
}

namespace Swoole\Http;

if (!class_exists('Swoole\Http\Server', false)) {
    class Server
    {
        public function __construct(string $host, int $port) {}
        public function set(array $options): void {}
        public function on(string $event, callable $callback): void {}
        public function start(): void {}
    }
}

if (!class_exists('Swoole\Http\Request', false)) {
    class Request
    {
        public array $server = [];
        public array $header = [];
        public array $get = [];
        public array $post = [];
        
        public function rawContent(): string { return ''; }
    }
}

if (!class_exists('Swoole\Http\Response', false)) {
    class Response
    {
        public function status(int $code): void {}
        public function header(string $name, string $value): void {}
        public function end(string $data): void {}
    }
}

// Global functions
if (!function_exists('go')) {
    function go(callable $callback): void {}
}

// Constants
if (!defined('SWOOLE_HOOK_ALL')) {
    define('SWOOLE_HOOK_ALL', 0);
}
