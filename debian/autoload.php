<?php

/**
 * Debian autoloader for php-vitexsoftware-ease-fluentpdo
 * 
 * This file ensures system FluentPDO is loaded before this package's classes
 */

require_once '/usr/share/php/Envms/FluentPDO/autoload.php';
require_once '/usr/share/php/Ease/autoload.php';

// Register this package's autoloader
spl_autoload_register(function ($class) {
    // Convert namespace to path
    $prefix = 'Ease\\SQL\\';
    $base_dir = __DIR__ . '/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // Also check for Logger classes
        $prefix = 'Ease\\Logger\\';
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        $base_dir = __DIR__ . '/Logger/';
    }
    
    // Get the relative class name
    $relative_class = substr($class, $len);
    
    // Replace namespace separators with directory separators
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

require_once '/usr/share/php/Composer/InstalledVersions.php';

(function (): void {
    $versions = [];
    foreach (\Composer\InstalledVersions::getAllRawData() as $d) {
        $versions = array_merge($versions, $d['versions'] ?? []);
    }
    $_cj  = @json_decode(@file_get_contents(__DIR__ . '/composer.json'), true);
    $name = defined('APP_NAME') ? APP_NAME : ($_cj['name'] ?? basename(__DIR__));
    $version = defined('APP_VERSION') ? APP_VERSION : '0.0.0';
    $versions[$name] = ['pretty_version' => $version, 'version' => $version,
        'reference' => null, 'type' => 'library', 'install_path' => __DIR__,
        'aliases' => [], 'dev_requirement' => false];
    \Composer\InstalledVersions::reload([
        'root' => ['name' => $name, 'pretty_version' => $version, 'version' => $version,
            'reference' => null, 'type' => 'project', 'install_path' => __DIR__,
            'aliases' => [], 'dev' => false],
        'versions' => $versions,
    ]);
})();
