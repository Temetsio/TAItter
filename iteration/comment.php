<?php
require_once 'config.php';
ini_set('display_errors', '0');
error_reporting(E_ALL);

while (ob_get_level()) {
  ob_end_clean();
}

header('Content-Type: application/json; charset=utf-8');

function json_exit($data, $code = 200) {
  http_response_code($code);
  echo json_encode($data);
  exit;
}

if (!function_exists('current_user_id') || !current_user_id()) {
  json_exit(['success' => false, 'error' => 'Unauthorized'], 401);
}

$postId = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$content = trim($_POST['content'] ?? '');

if (!$postId || $content === '') {
  json_exit(['success' => false, 'error' => 'Missing data'], 400);
}

if (mb_strlen($content) > 144) {
  json_exit(['success' => false, 'error' => 'Too long'], 400);
}

$userId = current_user_id();
$stmt = $mysqli->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
if (!$stmt) {
  error_log('comment.php prepare INSERT error: ' . ($mysqli->error ?? 'unknown'));
  json_exit(['success' => false, 'error' => 'DB error'], 500);
}

$bindOk = $stmt->bind_param("iis", $postId, $userId, $content);
if ($bindOk === false) {
  error_log('comment.php bind_param error: ' . ($stmt->error ?? 'unknown'));
  json_exit(['success' => false, 'error' => 'DB error'], 500);
}

$execOk = $stmt->execute();
if ($execOk === false) {
  error_log('comment.php execute error: ' . ($stmt->error ?? $mysqli->error ?? 'unknown'));
  json_exit(['success' => false, 'error' => 'DB error'], 500);
}

$stmt->close();
$stmt = $mysqli->prepare("SELECT COUNT(*) AS cnt FROM comments WHERE post_id = ?");
if (!$stmt) {
  error_log('comment.php prepare COUNT error: ' . ($mysqli->error ?? 'unknown'));
  json_exit(['success' => false, 'error' => 'DB error'], 500);
}

$stmt->bind_param("i", $postId);
$stmt->execute();
$res = $stmt->get_result();
if (!$res) {
  error_log('comment.php get_result error: ' . ($stmt->error ?? $mysqli->error ?? 'unknown'));
  json_exit(['success' => false, 'error' => 'DB error'], 500);
}

$row = $res->fetch_assoc();
$count = isset($row['cnt']) ? (int)$row['cnt'] : 0;
$stmt->close();

json_exit(['success' => true, 'count' => $count], 200);
