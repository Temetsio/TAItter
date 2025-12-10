<?php
session_start();

$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = ''; 
$DB_NAME = 'taitter';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    die("DB connect error: " . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}
function current_username() {
    return $_SESSION['username'] ?? null;
}
?>
