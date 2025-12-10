<?php
require_once 'config.php';

ini_set('display_errors', '0');
error_reporting(E_ALL);
while (ob_get_level()) ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

function json_exit($data, $code = 200) {
  http_response_code($code);
  echo json_encode($data);
  exit;
}

if (!function_exists('current_user_id') || !current_user_id()) {
  json_exit(['success' => false, 'error' => 'Unauthorized'], 401);
}

$commentId = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
if (!$commentId) {
  json_exit(['success' => false, 'error' => 'Missing comment_id'], 400);
}

$userId = current_user_id();


$stmt = $mysqli->prepare("SELECT user_id, post_id FROM comments WHERE comment_id = ?");
if (!$stmt) {
  error_log('delete_comment prepare SELECT error: ' . ($mysqli->error ?? 'unknown'));
  json_exit(['success' => false, 'error' => 'DB error'], 500);
}
$stmt->bind_param('i', $commentId);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
  json_exit(['success' => false, 'error' => 'Comment not found'], 404);
}
$row = $res->fetch_assoc();
$postId = (int)$row['post_id'];
if ((int)$row['user_id'] !== $userId) {
  json_exit(['success' => false, 'error' => 'Forbidden'], 403);
}
$stmt->close();


$stmt = $mysqli->prepare("DELETE FROM comments WHERE comment_id = ?");
if (!$stmt) {
  error_log('delete_comment prepare DELETE error: ' . ($mysqli->error ?? 'unknown'));
  json_exit(['success' => false, 'error' => 'DB error'], 500);
}
$stmt->bind_param('i', $commentId);
$ok = $stmt->execute();
if ($ok === false) {
  error_log('delete_comment execute error: ' . ($stmt->error ?? $mysqli->error ?? 'unknown'));
  json_exit(['success' => false, 'error' => 'DB error'], 500);
}
$stmt->close();

$stmt = $mysqli->prepare("SELECT COUNT(*) AS cnt FROM comments WHERE post_id = ?");
if ($stmt) {
  $stmt->bind_param('i', $postId);
  $stmt->execute();
  $res = $stmt->get_result();
  $row = $res->fetch_assoc();
  $count = isset($row['cnt']) ? (int)$row['cnt'] : 0;
  $stmt->close();
} else {
  $count = 0;
}

json_exit(['success' => true, 'count' => $count], 200);
