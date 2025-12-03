<?php
require_once 'config.php';
if(!current_user_id()) exit;

$type = $_GET['type'] ?? '';

$uid = current_user_id();

if ($type === 'mentions') {
    $mysqli->query("UPDATE users SET last_seen_mentions = NOW() WHERE user_id = $uid");
}

if ($type === 'shares') {
    $mysqli->query("UPDATE users SET last_seen_shares = NOW() WHERE user_id = $uid");
}
