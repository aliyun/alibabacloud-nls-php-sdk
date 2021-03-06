<?php

spl_autoload_register(function ($class) {
    $name = str_replace('WebSocket\\', '', $class);
    $file = __DIR__ . \DIRECTORY_SEPARATOR . 'lib' . \DIRECTORY_SEPARATOR . str_replace('\\', \DIRECTORY_SEPARATOR, $name) . '.php';
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
});

?>
