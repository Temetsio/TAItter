<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once 'config.php';

while (ob_get_level()) ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

register_shutdown_function(function() {
  $err = error_get_last();
  if ($err) {
    if (!headers_sent()) header('Content-Type: application/json; charset=utf-8', true, 500);
    echo json_encode(['success' => false, 'error' => 'Shutdown: ' . ($err['message'] ?? '')]);
  }
});
@file_put_contents(__DIR__ . '/debug_edit_comment.log', "---\n" . date('c') . "\nPOST: " . print_r($_POST, true) . "\nUSERID: " . (function_exists('current_user_id') ? current_user_id() : 'nf') . "\nHas mysqli: " . (isset($mysqli) ? 'yes' : 'no') . "\n", FILE_APPEND);

function json_exit($data, $code = 200) {
  http_response_code($code);
  echo json_encode($data);
  exit;
}

if (!function_exists('current_user_id') || !current_user_id()) {
  json_exit(['success' => false, 'error' => 'Unauthorized'], 401);
}

$commentId = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
$content = trim($_POST['content'] ?? '');

if (!$commentId || $content === '') {
  json_exit(['success' => false, 'error' => 'Missing data'], 400);
}

if (mb_strlen($content) > 144) {
  json_exit(['success' => false, 'error' => 'Too long'], 400);
}

$userId = current_user_id();

$stmt = $mysqli->prepare("SELECT user_id FROM comments WHERE comment_id = ?");
if (!$stmt) {
  error_log('edit_comment prepare SELECT error: ' . ($mysqli->error ?? 'unknown'));
  json_exit(['success' => false, 'error' => 'DB error'], 500);
}
$stmt->bind_param('i', $commentId);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
  json_exit(['success' => false, 'error' => 'Comment not found'], 404);
}
$row = $res->fetch_assoc();
if ((int)$row['user_id'] !== $userId) {
  json_exit(['success' => false, 'error' => 'Forbidden'], 403);
}
$stmt->close();

$stmt = $mysqli->prepare("UPDATE comments SET content = ?, edited_at = NOW() WHERE comment_id = ?");
if (!$stmt) {
  error_log('edit_comment prepare UPDATE error: ' . ($mysqli->error ?? 'unknown'));
  json_exit(['success' => false, 'error' => 'DB error'], 500);
}
$stmt->bind_param('si', $content, $commentId);
$ok = $stmt->execute();
if ($ok === false) {
  error_log('edit_comment execute error: ' . ($stmt->error ?? $mysqli->error ?? 'unknown'));
  json_exit(['success' => false, 'error' => 'DB error'], 500);
}
$stmt->close();

json_exit(['success' => true], 200);
