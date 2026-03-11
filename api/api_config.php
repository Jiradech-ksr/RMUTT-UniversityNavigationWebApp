<?php
require_once __DIR__ . '/../admin/includes/env_loader.php';
loadEnv(__DIR__ . '/../.env');

$api_base_url = getenv('API_BASE_URL');

if (!$api_base_url) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    $host = $_SERVER['HTTP_HOST'];
    $api_base_url = $protocol . "://" . $host . "/api";
}

define('API_URL', $api_base_url);
?>