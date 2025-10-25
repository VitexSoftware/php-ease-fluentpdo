<?php

/**
 * Debian autoloader for php-vitexsoftware-ease-fluentpdo
 * 
 * This file ensures system FluentPDO is loaded before this package's classes
 */

// Load system FluentPDO from Debian package
if (file_exists('/usr/share/php/Envms/FluentPDO/autoload.php')) {
    require_once '/usr/share/php/Envms/FluentPDO/autoload.php';
}

// Load Ease Core from Debian package
if (file_exists('/usr/share/php/EaseCore/autoload.php')) {
    require_once '/usr/share/php/EaseCore/autoload.php';
}

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
