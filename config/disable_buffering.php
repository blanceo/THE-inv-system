<?php
// disable_buffering.php
// Disable all output buffering
while (ob_get_level() > 0) {
    ob_end_clean();
}

// Disable Apache compression
if (function_exists('apache_setenv')) {
    apache_setenv('no-gzip', '1');
}

// Disable zlib compression
ini_set('zlib.output_compression', 'Off');

// Set headers to disable caching and buffering
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
?>