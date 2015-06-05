<?php

$autoload = realpath(__DIR__ . getenv('CLASS_AUTOLOADER_FROM_SRC_DIR'));

if (!is_file($autoload)) {
    realpath(__DIR__ . getenv('CLASS_AUTOLOADER_FROM_VENDOR_DIR'));
}

if (!is_file($autoload)) {
    die("Composer Autoload file not found.");
}

return require $autoload;
