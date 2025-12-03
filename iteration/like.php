<?php
require_once 'config.php';

if (!current_user_id()) {
    http_response_code(401);
    exit;
}

$postId = $_POST['post_id'] ?? null;

if (!$postId) {
    http_response_code(400);
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
    $stmt->execute();
    echo "unliked";
} else {
    $stmt = $mysqli->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $userId, $postId);
    $stmt->execute();
    echo "liked";
}