<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "universitynavigation_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    // DO NOT echo anything here, just die. Echoing breaks JSON.
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>