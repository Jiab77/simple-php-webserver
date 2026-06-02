#!/usr/bin/env php
<?php

/**
 * Unified High-Performance PHP Quick Web Server
 * 
 * Fuses Linux & MacOS optimization:
 * - Smart cross-platform CPU core detection
 * - High-concurrency worker threading (PHP_CLI_SERVER_WORKERS)
 * - Intelligent dual-mode argument parsing (Options flags & Classic positional args)
 * - Safe process launching (PCNTL with Passthru graceful fallback)
 * - Deluxe responsive directory listing & diagnostics dashboard
 */

// Define help option flags
$shortOptions = 'hi:p:c:d:v';
$longOptions = ['help', 'interface:', 'port:', 'cores:', 'directory:', 'verbose'];

$optind = 0;
$options = getopt($shortOptions, $longOptions, $optind);

// Base helper for fancy terminal colors
function format_color(string $text, string $colorCode): string
{
    $supportColor = DIRECTORY_SEPARATOR === '/' && (function_exists('posix_isatty') ? posix_isatty(STDOUT) : getenv('TERM'));
    return $supportColor ? "\033[{$colorCode}m{$text}\033[0m" : $text;
}

// Detect CPU cores across Windows, MacOS, Linux, BSD
function detect_cpu_cores(): int
{
    $cores = 4; // safe default fallback

    if (DIRECTORY_SEPARATOR === '\\') {
        $envCores = getenv('NUMBER_OF_PROCESSORS');
        if ($envCores !== false && is_numeric($envCores)) {
            $cores = (int)$envCores;
        }
    } else {
        if (is_executable('/usr/bin/nproc')) {
            $nproc = trim((string)shell_exec('nproc 2>/dev/null'));
            if (is_numeric($nproc)) {
                $cores = (int)$nproc;
            }
        } elseif (is_executable('/usr/sbin/sysctl') || is_executable('/usr/bin/sysctl')) {
            $sysctl = trim((string)shell_exec('sysctl -n hw.ncpu 2>/dev/null'));
            if (is_numeric($sysctl)) {
                $cores = (int)$sysctl;
            }
        } else {
            if (@file_exists('/proc/cpuinfo')) {
                $cpuinfo = file_get_contents('/proc/cpuinfo');
                preg_match_all('/^processor/m', $cpuinfo, $matches);
                $count = count($matches[0]);
                if ($count > 0) {
                    $cores = $count;
                }
            }
        }
    }
    return max(1, $cores);
}

// Configuration DTO
final class ServerConfiguration
{
    public string $script;
    public string $currentPath;
    public int $numberOfCores;
    public string $host;
    public int $port;
    public bool $debug;
    public string $pathToPhp;

    public function __construct(
        string $script,
        string $currentPath,
        int $numberOfCores,
        string $host,
        int $port,
        bool $debug,
        string $pathToPhp
    ) {
        $this->script = $script;
        $this->currentPath = $currentPath;
        $this->numberOfCores = $numberOfCores;
        $this->host = $host;
        $this->port = $port;
        $this->debug = $debug;
        $this->pathToPhp = $pathToPhp;
    }
}

// Parse input hybrid arguments
$detectedCores = detect_cpu_cores();
$positionalArgs = array_slice($argv, $optind);

// Check if help is requested
$isHelp = isset($options['h']) || isset($options['help']);
if (!$isHelp && !empty($positionalArgs)) {
    $firstArg = $positionalArgs[0];
    if (in_array($firstArg, ['help', '-h', '--help'])) {
        $isHelp = true;
    }
}

if ($isHelp) {
    echo PHP_EOL;
    echo format_color(" 🚀 Unified Quick Web Server ", "36;1") . PHP_EOL;
    echo " ======================================================================" . PHP_EOL;
    echo "  " . format_color("Usage (Options Mode):   ", "33") . " php " . basename(__FILE__) . " [options]" . PHP_EOL;
    echo "  " . format_color("Usage (Classic Mode):   ", "33") . " php " . basename(__FILE__) . " [interface:port] [/path/to/serve]" . PHP_EOL . PHP_EOL;
    
    echo "  " . format_color("Options:", "32") . PHP_EOL;
    echo "    -h, --help           Display this help documentation." . PHP_EOL;
    echo "    -i, --interface      Specify bound IP or domain (default: 127.0.0.1)" . PHP_EOL;
    echo "    -p, --port           Specify port number (default: 8000)" . PHP_EOL;
    echo "    -c, --cores          Number of CPU process threads (default: {$detectedCores})" . PHP_EOL;
    echo "    -d, --directory      Root directory folder (default: current folder)" . PHP_EOL;
    echo "    -v, --verbose        Enable debug logs and execution command review" . PHP_EOL . PHP_EOL;

    echo "  " . format_color("Examples:", "32") . PHP_EOL;
    echo "    php " . basename(__FILE__) . " -p 9090                       " . format_color("# Start on custom port 9090", "30;1") . PHP_EOL;
    echo "    php " . basename(__FILE__) . " 0.0.0.0:8000 /var/www         " . format_color("# Serve folder custom on all interfaces", "30;1") . PHP_EOL;
    echo "    php " . basename(__FILE__) . " ../public                     " . format_color("# Quick serve a relative directory", "30;1") . PHP_EOL;
    echo " ======================================================================" . PHP_EOL;
    exit(0);
}

// Set state parameters
$host = '127.0.0.1';
$port = 8000;
$directory = '';
$debug = isset($options['v']) || isset($options['verbose']);
$cores = $detectedCores;

// Use parsed options first
if (isset($options['i']) || isset($options['interface'])) {
    $host = $options['i'] ?? $options['interface'];
}
if (isset($options['p']) || isset($options['port'])) {
    $port = (int)($options['p'] ?? $options['port']);
}
if (isset($options['d']) || isset($options['directory'])) {
    $directory = $options['d'] ?? $options['directory'];
}
if (isset($options['c']) || isset($options['cores'])) {
    $cores = (int)($options['c'] ?? $options['cores']);
}

// Fallback to classic positional arguments
if (!empty($positionalArgs)) {
    $arg1 = $positionalArgs[0];
    
    // Check if the argument is host:port
    if (strpos($arg1, ':') !== false) {
        $parts = explode(':', $arg1);
        if (count($parts) > 2) {
            $lastColon = strrpos($arg1, ':');
            $hostPart = substr($arg1, 0, $lastColon);
            $portPart = substr($arg1, $lastColon + 1);
            $host = trim($hostPart, '[]');
            $port = (int)$portPart;
        } else {
            $host = $parts[0];
            $port = (int)$parts[1];
        }
    } elseif (is_numeric($arg1)) {
        $port = (int)$arg1;
    } elseif (is_dir($arg1)) {
        $directory = $arg1;
    } else {
        $host = $arg1;
    }

    // Capture second parameter as the directory
    if (isset($positionalArgs[1]) && empty($directory)) {
        $directory = $positionalArgs[1];
    }
}

// Post-resolving path
if (empty($directory)) {
    $directory = __DIR__;
} else {
    $realPath = realpath($directory);
    if ($realPath !== false) {
        $directory = $realPath;
    }
}

// Instantiate fully populated Config Object
$config = new ServerConfiguration(
    script: basename(__FILE__),
    currentPath: $directory,
    numberOfCores: max(1, $cores),
    host: $host,
    port: $port,
    debug: $debug,
    pathToPhp: defined('PHP_BINARY') && !empty(PHP_BINARY) ? PHP_BINARY : 'php'
);

// Create elegant landing index file if missing
function create_beautiful_index(string $path): void
{
    if (file_exists($path . '/index.php') || file_exists($path . '/index.html')) {
        return;
    }

    $template = <<<'PHP'
<?php
// Local PHP Web Server - Elegant Auto-generated Dashboard
if (isset($_GET['action']) && $_GET['action'] === 'phpinfo') {
    phpinfo();
    exit;
}

$dir = __DIR__;
$files = array_filter(scandir($dir), function($item) {
    return !in_array($item, ['.', '..', '.git', '.github', 'server.php', 'server.mac.php', 'index.disabled.html']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>⚡ Local PHP Server Dashboard</title>
    
    <!-- Fomantic UI CSS v2.9.4 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.4/semantic.min.css" integrity="sha512-ySrYzxj+EI1e9xj/kRYqeDL5l1wW0IWY8pzHNTIZ+vc1D3Z14UDNPbwup4yOUmlRemYjgUXsUZ/xvCQU2ThEAw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <style>
        body { 
            background-color: #0c0e17; 
            color: #e1e7f0; 
            font-family: 'Lato', 'Helvetica Neue', Arial, Helvetica, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .main-container { 
            margin-top: 4rem; 
            margin-bottom: 4rem; 
        }

        /* Ambient Glow & Segment Styling */
        .premium-card {
            background: rgba(17, 20, 34, 0.85) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5) !important;
            border-radius: 12px !important;
            backdrop-filter: blur(10px);
        }

        .neon-divider {
            height: 1px;
            background: linear-gradient(90deg, rgba(33, 133, 208, 0) 0%, rgba(33, 133, 208, 0.5) 50%, rgba(33, 133, 208, 0) 100%);
            margin: 2rem 0;
        }

        /* Glowing Pulse Indicator */
        .pulse-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #21ba45;
            box-shadow: 0 0 0 0 rgba(33, 186, 69, 0.7);
            animation: pulse-green 1.8s infinite;
            margin-right: 8px;
            vertical-align: middle;
        }

        @keyframes pulse-green {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(33, 186, 69, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 10px rgba(33, 186, 69, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(33, 186, 69, 0);
            }
        }

        /* File browser list styling */
        .file-list-container {
            max-height: 480px;
            overflow-y: auto;
            padding-right: 8px;
        }

        .file-item {
            background: rgba(255, 255, 255, 0.02) !important;
            border-radius: 6px !important;
            margin-bottom: 6px !important;
            border: 1px solid rgba(255, 255, 255, 0.03) !important;
            transition: all 0.2s ease-in-out !important;
        }

        .file-item:hover { 
            background: rgba(33, 133, 208, 0.12) !important; 
            border-color: rgba(33, 133, 208, 0.3) !important;
            transform: translateX(4px);
        }

        .file-link {
            color: #64b5f6 !important;
            font-weight: 600;
        }

        .file-link:hover {
            color: #2196f3 !important;
        }

        .folder-link {
            color: #ffe082 !important;
            font-weight: 600;
        }

        /* Modern styled scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(33, 133, 208, 0.4);
        }

        /* Glowing server banner link */
        .banner-glow {
            text-shadow: 0 0 15px rgba(33, 133, 208, 0.6);
        }

        /* Footer styling */
        .footer-text {
            color: #5a6578;
            font-size: 0.9rem;
            text-align: center;
            padding: 2rem 0;
        }
    </style>
</head>
<body>

    <div class="ui container main-container">
        <!-- Main Premium Dashboard Card -->
        <div class="ui inverted segment padded premium-card">
            
            <!-- Header Section -->
            <div class="ui grid stackable middle aligned">
                <div class="eleven wide column">
                    <h1 class="ui header inverted" style="margin: 0;">
                        <i class="server icon blue banner-glow"></i>
                        <div class="content">
                            <span class="banner-glow" style="color: #64b5f6;">Local PHP Server</span>
                            <div class="sub header" style="color: #8b9bb4; font-size: 1.05rem; margin-top: 0.5rem;">
                                <span class="pulse-indicator"></span>Engine host is online and routing requests safely.
                            </div>
                        </div>
                    </h1>
                </div>
                <!-- Dynamic Quick Navigation -->
                <div class="five wide column right aligned">
                    <div class="ui mini steps inverted" style="background: transparent; border: none; box-shadow: none;">
                        <div class="step active" style="background: transparent; padding: 0.5em 0;">
                            <i class="tachometer alternate icon teal"></i>
                            <div class="content">
                                <div class="title" style="color: #00b5ad; font-size: 0.85rem;">Dashboard Active</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="neon-divider"></div>
            
            <!-- Main Grid Layout -->
            <div class="ui grid stackable">
                <div class="row">
                    
                    <!-- Left Panel: Engine profile -->
                    <div class="six wide column" style="border-right: 1px solid rgba(255, 255, 255, 0.08);">
                        <h4 class="ui dividing header inverted teal">
                            <i class="microchip icon"></i> Engine Profile
                        </h4>
                        
                        <table class="ui inverted basic table celled unstackable" style="background: transparent;">
                            <tbody>
                                <tr>
                                    <td class="collapsing"><strong>PHP Build</strong></td>
                                    <td>
                                        <div class="ui label tiny black" style="border: 1px solid rgba(33, 186, 69, 0.4); color: #2ecc71;">
                                            <i class="code icon"></i> <?php echo PHP_VERSION; ?>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="collapsing"><strong>Core OS</strong></td>
                                    <td style="color: #b0bec5; font-size: 0.9rem;">
                                        <i class="terminal icon grey"></i> <?php echo PHP_OS . " (" . PHP_OS_FAMILY . ")"; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="collapsing"><strong>Root Path</strong></td>
                                    <td style="word-break: break-all; color: #90a4ae; font-family: monospace; font-size: 0.85rem;">
                                        <?php echo htmlspecialchars(__DIR__); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="collapsing"><strong>Concurrency</strong></td>
                                    <td>
                                        <div class="ui label tiny black" style="border: 1px solid rgba(0, 181, 173, 0.4); color: #00b5ad;">
                                            <i class="tasks icon"></i> <?php echo htmlspecialchars(getenv('PHP_CLI_SERVER_WORKERS') ?: '1'); ?> parallel threads
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div style="margin-top: 2rem;">
                            <a href="?action=phpinfo" target="_blank" class="ui button fluid inverted blue basic small">
                                <i class="info circle icon"></i> Inspect Complete phpinfo()
                            </a>
                        </div>
                    </div>

                    <!-- Right Panel: Space content browser -->
                    <div class="ten wide column">
                        <!-- Header with dynamic search -->
                        <div class="ui grid stackable middle aligned" style="margin-bottom: 1rem;">
                            <div class="eight wide column">
                                <h4 class="ui header inverted teal" style="margin: 0;">
                                    <i class="folder open icon animate-folder"></i>
                                    <div class="content">
                                        Directory Browser
                                        <div class="sub header" style="color: #8b9bb4; font-size: 0.8rem;">Explore files in document root</div>
                                    </div>
                                </h4>
                            </div>
                            <div class="eight wide column right aligned">
                                <div class="ui transparent inverted icon input" style="border-bottom: 1px solid rgba(255,255,255,0.15); width: 100%; max-width: 220px; font-size: 0.9rem;">
                                    <input type="text" id="file-search" placeholder="Filter files instantly...">
                                    <i class="search link icon teal"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Content file browser -->
                        <?php if (empty($files)): ?>
                            <div class="ui placeholder segment inverted" style="background: rgba(255,255,255,0.01) !important; border: 1px dashed rgba(255,255,255,0.1) !important; box-shadow: none;">
                                <div class="ui icon header">
                                    <i class="file code outline icon grey" style="opacity: 0.6;"></i>
                                    No public files found.
                                    <div class="sub header" style="margin-top: 0.5rem; color: #5a6578;">Your public root is completely clean!</div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="file-list-container">
                                <div class="ui selection list inverted relaxed middle aligned">
                                    <?php foreach ($files as $file): 
                                        $isDir = is_dir($dir . '/' . $file);
                                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                        
                                        // Precise mapping for nice premium folder/file colors
                                        $icon = "file outline icon";
                                        $colorClass = "grey";
                                        
                                        if ($isDir) {
                                            $icon = "folder open yellow icon";
                                        } else {
                                            switch($ext) {
                                                case 'php':
                                                    $icon = "file code icon";
                                                    $colorClass = "blue";
                                                    break;
                                                case 'html':
                                                case 'htm':
                                                    $icon = "file code outline icon";
                                                    $colorClass = "teal";
                                                    break;
                                                case 'js':
                                                    $icon = "js icon";
                                                    $colorClass = "yellow";
                                                    break;
                                                case 'css':
                                                    $icon = "css3 alternate icon";
                                                    $colorClass = "pink";
                                                    break;
                                                case 'json':
                                                    $icon = "file alternate outline icon";
                                                    $colorClass = "orange";
                                                    break;
                                                case 'md':
                                                    $icon = "markdown icon";
                                                    $colorClass = "violet";
                                                    break;
                                                case 'png':
                                                case 'jpg':
                                                case 'jpeg':
                                                case 'gif':
                                                case 'svg':
                                                case 'webp':
                                                case 'ico':
                                                    $icon = "file image outline icon";
                                                    $colorClass = "purple";
                                                    break;
                                                case 'zip':
                                                case 'tar':
                                                case 'gz':
                                                case 'rar':
                                                    $icon = "file archive outline icon";
                                                    $colorClass = "red";
                                                    break;
                                            }
                                        }
                                    ?>
                                        <div class="item file-item" style="padding: 0.85em 1.1em !important;">
                                            <i class="large <?php echo $icon; ?> <?php echo $colorClass; ?> middle aligned" style="opacity: 0.9;"></i>
                                            <div class="content">
                                                <?php if ($isDir): ?>
                                                    <span class="header folder-link"><?php echo htmlspecialchars($file); ?>/</span>
                                                <?php else: ?>
                                                    <a class="header file-link" href="<?php echo htmlspecialchars($file); ?>"><?php echo htmlspecialchars($file); ?></a>
                                                <?php endif; ?>
                                                <div class="description" style="font-size: 0.8rem; margin-top: 0.25rem; color: #8893a7;">
                                                    <?php echo $isDir ? 'Directory' : round(filesize($dir . '/' . $file) / 1024, 2) . ' KB'; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Beautiful, clean premium footer -->
    <div class="footer-text">
        ⚡ Engine powered by <strong>Simple PHP Web Server</strong> • Tailored in Obsidian Dark Theme with 💙 <br>
        <span style="font-size: 0.8rem; margin-top: 0.5rem; display: inline-block; opacity: 0.5;">Fomantic UI v2.9.4 Secured via SRI Subresource Integrity</span>
    </div>

    <!-- jQuery and Fomantic UI Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.4/semantic.min.js" integrity="sha512-Y/wIVu+S+XJsDL7I+nL50kAVFLMqSdvuLqF2vMoRqiMkmvcqFjEpEgeu6Rx8tpZXKp77J8OUpMKy0m3jLYhbbw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        $(document).ready(function() {
            // Real-Time browser client searching filter
            $('#file-search').on('input', function() {
                var value = $(this).val().trim().toLowerCase();
                if (value === "") {
                    $('.file-item').show();
                    return;
                }
                $('.file-item').each(function() {
                    var text = $(this).find('.header').text().toLowerCase();
                    if (text.includes(value)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Smooth subtle animation when hover icon
            $('.file-item').hover(
                function() {
                    $(this).find('i.icon').addClass('bounce');
                },
                function() {
                    $(this).find('i.icon').removeClass('bounce');
                }
            );
        });
    </script>
</body>
</html>
PHP;

    file_put_contents($path . '/index.php', $template);
}

// Trigger automatic dashboard generation if folder index is empty
create_beautiful_index($config->currentPath);

// Console output display
echo PHP_EOL;
echo format_color(" ⚡ Self PHP Web Server • v1.0.0 (Unified Edition)", "36;1") . PHP_EOL;
echo " ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . PHP_EOL;

if ($config->debug) {
    echo " " . format_color("•", "30;1") . " " . format_color("Debug Mode:", "30;1") . " Active" . PHP_EOL;
    echo " " . format_color("•", "30;1") . " " . format_color("Raw Executable:", "30;1") . " " . $config->pathToPhp . PHP_EOL;
    echo " " . format_color("•", "30;1") . " " . format_color("System Platform:", "30;1") . " " . PHP_OS . " (" . PHP_OS_FAMILY . ")" . PHP_EOL;
    echo " " . format_color("•", "30;1") . " " . format_color("Allocated Workers:", "30;1") . " " . $config->numberOfCores . PHP_EOL;
    echo " " . format_color("•", "30;1") . " " . format_color("Arguments Parsed:", "30;1") . " " . implode(', ', $argv) . PHP_EOL;
}

echo " " . format_color("•", "32") . " " . format_color("Address Interface:", "32") . " " . format_color($config->host, "1") . PHP_EOL;
echo " " . format_color("•", "32") . " " . format_color("Target Listen Port:", "32") . " " . format_color((string)$config->port, "1") . PHP_EOL;
echo " " . format_color("•", "32") . " " . format_color("Document Root Dir:", "32") . " " . format_color($config->currentPath, "1") . PHP_EOL;
echo " " . format_color("•", "35") . " " . format_color("Selected Threading:", "35") . " " . format_color("{$config->numberOfCores} Parallel Workers", "1;35") . PHP_EOL;
echo " ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . PHP_EOL;
echo PHP_EOL;

echo format_color(" 🚀 Server initializing at URL: ", "32") . format_color("http://{$config->host}:{$config->port}", "36;1") . PHP_EOL;
echo format_color(" 🛑 Send [Ctrl + C] anytime to gracefully shutdown task...", "31") . PHP_EOL;
echo PHP_EOL;

// Setup concurrency worker environment variables
// PHP 7.4+ supports multi-threaded requests handling via this built-in environment variable
putenv("PHP_CLI_SERVER_WORKERS=" . $config->numberOfCores);
$_ENV['PHP_CLI_SERVER_WORKERS'] = (string)$config->numberOfCores;

$arguments = ['-S', $config->host . ':' . $config->port];
if (!empty($config->currentPath)) {
    array_push($arguments, '-t', $config->currentPath);
}

// Safe launching sequence with graceful PCNTL/Passthru fallback
if (function_exists('pcntl_exec')) {
    pcntl_exec($config->pathToPhp, $arguments, ['PHP_CLI_SERVER_WORKERS' => (string)$config->numberOfCores]);
} else {
    $cmdArgs = array_map('escapeshellarg', $arguments);
    $cmd = escapeshellcmd($config->pathToPhp) . ' ' . implode(' ', $cmdArgs);
    passthru($cmd);
}
