<?php
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

if (!current_user_id()) {
  http_response_code(401);
  echo json_encode(['success'=>false,'error'=>'Unauthorized']);
  exit;
}

$postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
if (!$postId) {
  http_response_code(400);
  echo json_encode(['success'=>false,'error'=>'Missing post_id']);
  exit;
}

$stmt = $mysqli->prepare("
  SELECT 
    c.comment_id, 
    c.content,

    -- ★ Changed here: Finnish format date
    DATE_FORMAT(c.created_at, '%d.%m.%Y %H:%i') AS created_at,

    c.edited_at, 
    u.user_id, 
    u.username, 
    u.profile_picture_url
  FROM comments c
  JOIN users u ON c.user_id = u.user_id
  WHERE c.post_id = ?
  ORDER BY c.created_at ASC
  LIMIT 200
");
$stmt->bind_param("i", $postId);
$stmt->execute();
$res = $stmt->get_result();

$comments = [];
while ($row = $res->fetch_assoc()) {
  $comments[] = [
    'comment_id' => (int)$row['comment_id'],
    'user_id' => (int)$row['user_id'],
    'username' => $row['username'],
    'content' => $row['content'],
    'created_at' => $row['created_at'], // already formatted
    'edited_at' => $row['edited_at'] ?? null
  ];
}

echo json_encode(['success'=>true,'comments'=>$comments]);
