<?php
/**
 * Plugin Asset Handler
 * Serves static assets (CSS, JS, images) from plugin directories
 */

// Get parameters
$plugin = $_GET['plugin'] ?? '';
$asset = $_GET['asset'] ?? '';

// Validate parameters
if (empty($plugin) || empty($asset)) {
    http_response_code(400);
    exit('Bad Request');
}

// Sanitize plugin name (only allow alphanumeric, dash, underscore)
if (!preg_match('/^[a-zA-Z0-9_-]+$/', $plugin)) {
    http_response_code(400);
    exit('Invalid plugin name');
}

// Sanitize asset path (prevent directory traversal)
$asset = str_replace(['../', '..\\'], '', $asset);
if (strpos($asset, '..') !== false) {
    http_response_code(400);
    exit('Invalid asset path');
}

// Build file path
$pluginDir = dirname(__DIR__) . '/plugins/' . $plugin;
$assetPath = $pluginDir . '/assets/' . $asset;

// Check if file exists and is within plugin directory
if (!file_exists($assetPath) || !is_file($assetPath)) {
    http_response_code(404);
    exit('Asset not found');
}

// Verify the resolved path is within the plugin directory (security check)
$realPluginDir = realpath($pluginDir);
$realAssetPath = realpath($assetPath);
if (!$realPluginDir || !$realAssetPath || strpos($realAssetPath, $realPluginDir) !== 0) {
    http_response_code(403);
    exit('Access denied');
}

// Get file info
$fileExtension = strtolower(pathinfo($assetPath, PATHINFO_EXTENSION));
$fileSize = filesize($assetPath);
$lastModified = filemtime($assetPath);

// Set appropriate Content-Type header based on file extension
$contentTypes = [
    'css' => 'text/css',
    'js' => 'application/javascript',
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif' => 'image/gif',
    'svg' => 'image/svg+xml',
    'ico' => 'image/x-icon',
    'woff' => 'font/woff',
    'woff2' => 'font/woff2',
    'ttf' => 'font/ttf',
    'eot' => 'application/vnd.ms-fontobject'
];

$contentType = $contentTypes[$fileExtension] ?? 'application/octet-stream';

// Set caching headers for better performance
$maxAge = 3600; // 1 hour
header('Content-Type: ' . $contentType);
header('Content-Length: ' . $fileSize);
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
header('Cache-Control: public, max-age=' . $maxAge);
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $maxAge) . ' GMT');

// Handle conditional requests (304 Not Modified)
$ifModifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '';
if ($ifModifiedSince && strtotime($ifModifiedSince) >= $lastModified) {
    http_response_code(304);
    exit;
}

// Output the file
readfile($assetPath);