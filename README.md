# ⚡ Unified Quick PHP Web Server

A high-performance, single-file development web server powered by PHP. 

This is an optimized and unified version that merges **Linux & macOS implementations** into a single, highly portable, cross-platform utility. It works flawlessly under **Linux, MacOS, BSD, and Windows/Termux**.

---

## ✨ Features

* **🚀 High Concurrency (Multi-threaded Processing)**: Automatically detects your total CPU cores (`nproc` / `sysctl`) and configures `PHP_CLI_SERVER_WORKERS` dynamically to serve parallel asset requests at lightning speeds.
* **🔧 Intelligent Dual-Mode Parser**:
  - **Options Mode**: Configure specific flags like a pro: `-i localhost -p 9000 -c 8 -d ./my-folder`.
  - **Classic Mode**: Backwards-compatible quick start structure: `./server.php [interface:port] [/path/to/serve]`.
* **🎨 Beautiful Out-Of-The-Box Dashboard**: If your target directory lacks an `index.html` or `index.php`, the server automatically generates a gorgeous, fully-responsive dashboard using **Fomantic UI**. It displays:
  - System environment metrics (PHP version, OS signature, loaded threads).
  - An interactive **Directory Space Browser** (allowing you to click and open any HTML, PHP, or media assets instantly in your browser).
  - A secure link to standard `phpinfo()` diagnostics with zero folder pollution.
* **🛡️ Bulletproof Process Launching**: Automatically leverages Unix `pcntl_exec` to replace the execution thread cleanly (no dangling zombie processes). If `pcntl` is unavailable (such as Windows, Termux, or restricted setups), it falls back gracefully to a solid shell pass-through.
* **🧬 Zero Dependencies**: Standard pure PHP, compatible across all runtimes from PHP 7.4 up to PHP 8.5+.

---

## 📖 Usage

Make the script executable once:
```console
$ chmod +x web/server.php
```

### 1. Classic Mode (Quick Start)

Run with standard defaults (`127.0.0.1:8000` serving the script's directory):
```console
$ ./web/server.php
```

Specify a custom address or port:
```console
$ ./web/server.php 8080
$ ./web/server.php 0.0.0.0:3000
```

Specify a custom directory:
```console
$ ./web/server.php 127.0.0.1:8000 /path/to/public
```

---

### 2. Options Mode (Advanced Tuning)

Use powerful CLI options flags:
```console
$ ./web/server.php --interface=0.0.0.0 --port=9000 --cores=8 --directory=./public
```

Or using short flags:
```console
$ ./web/server.php -i 0.0.0.0 -p 9000 -c 8 -d ./public
```

### Options Reference
```text
  -h, --help           Display the help documentation.
  -i, --interface      Specify bound IP or domain (Default: 127.0.0.1)
  -p, --port           Specify port number (Default: 8000)
  -c, --cores          Number of CPU process threads (Default: Automatic core count)
  -d, --directory      Root directory folder (Default: Server folder)
  -v, --verbose        Enable debug logs and active runtime metrics
```

---

## 👥 Contributors & Credits

Special thanks to the authors who made this unification possible:

* **[@staatzstreich](https://github.com/staatzstreich)**: Who designed the elegant object-oriented macOS implementation and argument structure.
* **Unified Development Team**: Merged both scripts into a portable, fallbacked command-line utility with a rich directory browser dashboard.
* **Jarvis o/b/o Gemini**: Acted as the AI co-pilot, refining the architecture, streamlining compatibility, and crafting the interactive interface design.

---

🤖 **Note:** This unified cross-platform version was engineered in perfect pairing harmony using: **[ai-pipeline](https://github.com/Jiab77/ai-pipeline)**. All honor and credit to the master orchestrator! 🚀
