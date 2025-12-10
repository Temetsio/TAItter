<?php
require_once 'config.php';
header('Content-Type: application/json');

if (!current_user_id()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$postId = $_POST['post_id'] ?? null;

if (!$postId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing post_id']);
    exit;
}

$userId = current_user_id();
$stmt = $mysqli->prepare("SELECT like_id FROM likes WHERE user_id = ? AND post_id = ?");
$stmt->bind_param("ii", $userId, $postId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt = $mysqli->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
    $stmt->bind_param("ii", $userId, $postId);
    
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => 'Database error']);
        exit;
    }

    $stmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM likes WHERE post_id = ?");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['cnt'];
    
    echo json_encode([
        'success' => true,
        'action' => 'unliked', 
        'count' => (int)$count,
        'hasLiked' => false
    ]);
} else {
    $stmt = $mysqli->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $userId, $postId);
    
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => 'Database error']);
        exit;
    }
    
    $stmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM likes WHERE post_id = ?");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['cnt'];
    
    echo json_encode([
        'success' => true,
        'action' => 'liked', 
        'count' => (int)$count,
        'hasLiked' => true
    ]);
}
exit;

