<?php
require_once 'config.php';
if (!current_user_id()) { http_response_code(403); echo "Login required"; exit; }

$post_id = intval($_POST['post_id'] ?? 0);
if (!$post_id) { header('Location: index.php'); exit; }

$stmt = $mysqli->prepare("INSERT INTO reposts (user_id, post_id) VALUES (?, ?)");
$uid = current_user_id();
$stmt->bind_param('ii', $uid, $post_id);
$stmt->execute();
header('Location: index.php');
exit;
?>
