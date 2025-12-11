<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!current_user_id()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$postIds = $input['post_ids'] ?? [];

if (empty($postIds) || !is_array($postIds)) {
    echo json_encode(['success' => true, 'likes' => []]);
    exit;
}

$userId = current_user_id();
$likes = [];

foreach ($postIds as $postId) {
    if (!is_numeric($postId)) continue;
    
    // Hae tykkäysten määrä
    $stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM likes WHERE post_id = ?");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['count'];
    
    // Tarkista onko käyttäjä tykännyt
    $stmt = $mysqli->prepare("SELECT COUNT(*) as liked FROM likes WHERE post_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $postId, $userId);
    $stmt->execute();
    $userLiked = $stmt->get_result()->fetch_assoc()['liked'] > 0;
    
    $likes[$postId] = [
        'count' => $count,
        'user_liked' => $userLiked
    ];
}

echo json_encode([
    'success' => true,
    'likes' => $likes
]);