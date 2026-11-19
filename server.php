<?php

/*
 * Custom router for PHP's built-in server (used by `php artisan serve`).
 * Malformed path combinations such as ///.../// are rejected by the built-in
 * server before Laravel can handle them, so catch them here and return the
 * dashboard. Everything else is passed to Laravel's front controller.
 */

$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

if (preg_match('#/{2,}|\.{2,}#', $uri)) {
    header('Location: /dashboard', true, 302);
    exit;
}

if ($uri !== '/' && file_exists($publicPath.$uri)) {
    return false;
}

$formattedDateTime = date('D M j H:i:s Y');

$requestMethod = $_SERVER['REQUEST_METHOD'];
$remoteAddress = $_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'];

file_put_contents('php://stdout', "[$formattedDateTime] $remoteAddress [$requestMethod] URI: $uri\n");

require_once $publicPath.'/index.php';
