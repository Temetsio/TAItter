<?php
require_once 'config.php';

if (!current_user_id()) {
    http_response_code(401);
    exit;
}

$type = $_GET['type'] ?? null;
$uid = current_user_id();

if ($type === 'mentions') {
    $stmt = $mysqli->prepare("UPDATE users SET last_seen_mentions = NOW() WHERE user_id = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
} elseif ($type === 'shares') {
    $stmt = $mysqli->prepare("UPDATE users SET last_seen_shares = NOW() WHERE user_id = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
} elseif ($type === 'likes') {
    $stmt = $mysqli->prepare("UPDATE users SET last_seen_likes = NOW() WHERE user_id = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
}

echo "OK";