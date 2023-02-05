#!/usr/bin/env php

<?php
// Defaults
$debug = true;
$script = basename(__FILE__);
$current_path = __DIR__;
$nproc = trim(`nproc`);
$default_interface = '127.0.0.1';
$default_port = 8000;
$delete_fake_index = false;

// User settings
if ($argc >= 2) {
    $network_access = explode(':', escapeshellarg($argv[1]));
    if (count($network_access) === 2) {
        $user_interface = $network_access[0];
        $user_port = (int)$network_access[1];
    } else {
        $user_port = (int)str_replace("'", "", escapeshellarg($argv[1]));
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
    global $default_interface,
        $default_port,
        $user_interface,
        $user_port,
        $nproc,
        $argc,
        $argv;

    echo 'Starting web server...' . PHP_EOL;
    echo 'Press [Ctrl + C] to stop it.' . PHP_EOL;
    echo PHP_EOL;
    if ($argc === 3) {
        pcntl_exec(
            trim(`which php`),
            ['-S', (isset($user_interface) ? $user_interface : $default_interface) . ':' . (isset($user_port) ? $user_port : $default_port), '-t', $argv[2]],
            ['PHP_CLI_SERVER_WORKERS' => $nproc]
        );
    } else {
        pcntl_exec(
            trim(`which php`),
            ['-S', (isset($user_interface) ? $user_interface : $default_interface) . ':' . (isset($user_port) ? $user_port : $default_port)],
            ['PHP_CLI_SERVER_WORKERS' => $nproc]
        );
    }
}

// Display
echo $script . ' - Self PHP Web Server' . PHP_EOL . PHP_EOL;
if ($argc >= 2 && ($argv[1] === '-h' || $argv[1] === '--help')) {
    echo ' - Usage: ' . $script . ' [interface:port] [/path/to/serve]' . PHP_EOL . PHP_EOL;
    echo ' - System Info:' . PHP_EOL;
    echo "\t" . '- Current Path: ' . $current_path . PHP_EOL;
    echo "\t" . '- CPU Cores: ' . $nproc . PHP_EOL;
    echo "\t" . '- OS: ' . PHP_OS . PHP_EOL;
    echo "\t" . '- PHP: ' . PHP_VERSION . PHP_EOL;
    echo PHP_EOL;
    exit(1);
}
if ($debug === true) {
    echo ' - Debug:' . PHP_EOL;
    echo "\t" . ' - Args: ' . implode(',', $argv) . PHP_EOL;
    echo "\t" . ' - Count: ' . $argc . PHP_EOL;
    echo "\t" . ' - Env: PHP_CLI_SERVER_WORKERS=' . $nproc . PHP_EOL;
    if ($argc === 3) {
        echo "\t" . ' - Command: ' . trim(`which php`) . ' -S ' . (isset($user_interface) ? $user_interface : $default_interface) . ':' . (isset($user_port) ? $user_port : $default_port) . ' -t ' . $argv[2] . PHP_EOL;
    } else {
        echo "\t" . ' - Command: ' . trim(`which php`) . ' -S ' . (isset($user_interface) ? $user_interface : $default_interface) . ':' . (isset($user_port) ? $user_port : $default_port) . PHP_EOL;
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
