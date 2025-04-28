<?php
function getBaseURL() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || 
                 $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

    $host = $_SERVER['HTTP_HOST']; // like localhost
    $script = $_SERVER['SCRIPT_NAME']; // like /your-project/folder/file.php
    $path = str_replace(basename($script), '', $script); // remove file.php, get /your-project/
    $url = trim($protocol) . trim($host) . trim($path);
    return preg_replace('#(?<!:)//+#', '/', $url);
}
