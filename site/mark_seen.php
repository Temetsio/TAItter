

<?php
require_once 'config.php';
if (!current_user_id()) exit;

$type = $_GET['type'] ?? null;
$uid = current_user_id();

$allowed = [
  'mentions' => 'last_seen_mentions',
  'shares'   => 'last_seen_shares',
  'likes'    => 'last_seen_likes'
];

if (!isset($allowed[$type])) exit;

$col = $allowed[$type];

$stmt = $mysqli->prepare("UPDATE users SET $col = NOW() WHERE user_id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
echo "OK";