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
