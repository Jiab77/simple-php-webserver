#!/usr/bin/env php

<?php
// Defaults
$config = new StdClass;
$config->debug = true;
$config->version = '0.1.0';
$config->script = basename(__FILE__);
$config->current_path = __DIR__;
$config->nproc = trim(`nproc`);
$config->default_interface = '127.0.0.1';
$config->default_port = 8000;
$config->delete_fake_index = false;

// User settings
if ($argc >= 2) {
    $network_access = explode(':', escapeshellarg($argv[1]));
    if (count($network_access) === 2) {
        $config->user_interface = $network_access[0];
        $config->user_port = (int)$network_access[1];
    } else {
        $config->user_interface = $config->default_interface;
        $config->user_port = (int)str_replace("'", "", escapeshellarg($argv[1]));
    }
}

// Functions
function create_fake_index($path = null)
{
    if (is_null($path)) {
        $path = __DIR__;
    }
    if (!file_exists($path . '/index.php') && !file_exists($path . '/index.html')) {
        file_put_contents($path . '/index.php', '<?php phpinfo(); ?>');
    }
}
function run_server()
{
    global $config, $argc, $argv;

    echo 'Starting web server...' . PHP_EOL;
    echo 'Press [Ctrl + C] to stop it.' . PHP_EOL;
    echo PHP_EOL;
    if ($argc === 3) {
        pcntl_exec(
            trim(`which php`),
            ['-S', (isset($config->user_interface) ? $config->user_interface : $config->default_interface) . ':' . (isset($config->user_port) ? $config->user_port : $config->default_port), '-t', $argv[2]],
            ['PHP_CLI_SERVER_WORKERS' => $config->nproc]
        );
    } else {
        pcntl_exec(
            trim(`which php`),
            ['-S', (isset($config->user_interface) ? $config->user_interface : $config->default_interface) . ':' . (isset($config->user_port) ? $config->user_port : $config->default_port)],
            ['PHP_CLI_SERVER_WORKERS' => $config->nproc]
        );
    }
}

// Display
echo $config->script . ' - Self PHP Web Server - v' . $config->version . PHP_EOL . PHP_EOL;
if ($argc >= 2 && ($argv[1] === '-h' || $argv[1] === '--help')) {
    echo ' - Usage: ' . $config->script . ' [interface:port] [/path/to/serve]' . PHP_EOL . PHP_EOL;
    echo ' - System Info:' . PHP_EOL;
    echo "\t" . '- Current Path: ' . $config->current_path . PHP_EOL;
    echo "\t" . '- CPU Cores: ' . $config->nproc . PHP_EOL;
    echo "\t" . '- OS: ' . PHP_OS . PHP_EOL;
    echo "\t" . '- PHP: ' . PHP_VERSION . PHP_EOL;
    echo PHP_EOL;
    exit(1);
}
if ($config->debug === true) {
    echo ' - System Info:' . PHP_EOL;
    echo "\t" . '- Current Path: ' . $config->current_path . PHP_EOL;
    echo "\t" . '- CPU Cores: ' . $config->nproc . PHP_EOL;
    echo "\t" . '- OS: ' . PHP_OS . PHP_EOL;
    echo "\t" . '- PHP: ' . PHP_VERSION . PHP_EOL;
    echo PHP_EOL;
    echo ' - Debug:' . PHP_EOL;
    echo "\t" . ' - Args: ' . implode(',', $argv) . PHP_EOL;
    echo "\t" . ' - Count: ' . $argc . PHP_EOL;
    echo "\t" . ' - Env: PHP_CLI_SERVER_WORKERS=' . $config->nproc . PHP_EOL;
    if ($argc === 3) {
        echo "\t" . ' - Command: ' . trim(`which php`) . ' -S ' . (isset($config->user_interface) ? $config->user_interface : $config->default_interface) . ':' . (isset($config->user_port) ? $config->user_port : $config->default_port) . ' -t ' . $argv[2] . PHP_EOL;
    } else {
        echo "\t" . ' - Command: ' . trim(`which php`) . ' -S ' . (isset($config->user_interface) ? $config->user_interface : $config->default_interface) . ':' . (isset($config->user_port) ? $config->user_port : $config->default_port) . PHP_EOL;
    }
    echo PHP_EOL;
}

// Check if web root or document is given
if ($argc >= 2) {
    // If nothing is given, create a fake index file
    if ($argc === 3) {
        create_fake_index($argv[2]);
    }
    if ($argc === 2) {
        create_fake_index();
    }
}

// Run web server
echo ' - Output:' . PHP_EOL;
echo PHP_EOL;
run_server();
echo PHP_EOL;
