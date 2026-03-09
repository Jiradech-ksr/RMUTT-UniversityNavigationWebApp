<?php
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";

$host = $_SERVER['HTTP_HOST'];

$base_ip = $protocol . "://" . $host;

$upload_path = "/UniversityNavigationWebApp/uploads/reports/";
$full_base_url = $base_ip . $upload_path;
?>