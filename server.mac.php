#!/usr/bin/env php

<?php

// Check for min. PHP Version
PHP_VERSION_ID > 80100 ?: die('You need PHP >= 8.1.0 to run this script') . PHP_EOL;

// Data Transfer Object for overall configuration
class ConfigurationDTO
{
    public static string $shortOptions = 'i::p::n::d::h::';
    public static array $longOptions = ['interface::', 'port::', 'cores::', 'directory::', 'help::'];

    public function __construct(
        public readonly bool $debug = true,
        public readonly string $script = '',
        public readonly ?string $currentPath = '',
        public readonly string $numberOfCores = '',
        public readonly string $host = '127.0.0.1',
        public readonly int $port = 8000,
        public readonly bool $deleteFakeIndex = false,
        public readonly string $pathToPhp = '',
    ) {
    }
}

// Parse commandline
$options = getopt(ConfigurationDTO::$shortOptions, ConfigurationDTO::$longOptions);

// Create configuration based on commandline parameters
$config = new ConfigurationDTO(
    script: basename(__FILE__),
    currentPath: $options['d'] ?? $options['directory'] ??= '',
    numberOfCores: $options['n'] ?? $options['cores'] ??= trim(`sysctl -n hw.ncpu`),
    host: $options['i'] ?? $options['interface'] ??= '127.0.0.1',
    port: $options['p'] ?? $options['port'] ??= 8000,
    pathToPhp: trim(`which php`),
);

// Functions
function create_fake_index(string $path = null): void
{
    $path = $path === null || empty($path) ? __DIR__ : $path;

    if (!file_exists($path . '/index.php') && !file_exists($path . '/index.html')) {
        file_put_contents($path . '/index.php', '<?php phpinfo(); ?>');
        file_put_contents($path . '/index.html', '');
    }
}

function run_server(ConfigurationDTO $config): void
{
    echo 'Starting web server...' . PHP_EOL;
    echo 'Press [Ctrl + C] to stop it.' . PHP_EOL;
    echo PHP_EOL;

    $arguments = ['-S', $config->host . ':' . $config->port];
    if (empty($config->currentPath) === false) {
        $subarguments = ['-t', $config->currentPath];
        array_push($arguments, ...$subarguments);
    }

    pcntl_exec(
        trim(`which php`),
        $arguments,
        ['PHP_CLI_SERVER_WORKERS' => $config->numberOfCores]
    );
}

// Display
echo $config->script . ' - Self PHP Web Server' . PHP_EOL . PHP_EOL;
if ((isset($options['h']) && $options['h'] === false) || (isset($options['help']) && $options['help'] === false)) {
    echo " - Usage: $config->script [options]" . PHP_EOL . PHP_EOL;
    echo "Options:" . PHP_EOL;
    echo "\t-h, --help\t\tDisplay help for the given command." . PHP_EOL;
    echo "\t-i, --interface\t\tIP - Adress for server." . PHP_EOL;
    echo "\t-p, --port\t\tPort for server." . PHP_EOL;
    echo "\t-c, --cores\t\tNumber of processor - cores to use." . PHP_EOL;
    echo "\t-d, --directory\t\tPublic folder for server." . PHP_EOL;
    echo PHP_EOL;
    echo ' - System Info:' . PHP_EOL;
    echo "\t - Current Path: $config->currentPath" . PHP_EOL;
    echo "\t - CPU Cores:  $config->numberOfCores" . PHP_EOL;
    echo "\t - OS: " . PHP_OS . PHP_EOL;
    echo "\t - PHP: " . PHP_VERSION . PHP_EOL;
    echo PHP_EOL;
    exit(1);
}

if ($config->debug === true) {
    echo ' - Debug:' . PHP_EOL;
    echo "\t - Args   : " . implode(',', $argv) . PHP_EOL;
    echo "\t - Count  : $argc" . PHP_EOL;
    echo "\t - Env    : PHP_CLI_SERVER_WORKERS=$config->numberOfCores" . PHP_EOL;
    if (empty($config->currentPath) === false) {
        echo "\t - Command: $config->pathToPhp -S $config->host:$config->port -t $config->currentPath" . PHP_EOL;
    } else {
        echo "\t - Command: $config->pathToPhp -S $config->host:$config->port" . PHP_EOL;
    }
    echo PHP_EOL;
}

// Check if web root or document is given
// If nothing is given, create a fake index file
create_fake_index($config->currentPath);

// Run web server
echo ' - Output:' . PHP_EOL;
echo PHP_EOL;
run_server($config);
echo PHP_EOL;
