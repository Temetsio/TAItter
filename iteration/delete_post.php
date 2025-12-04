<?php
require_once 'config.php';

if (!current_user_id()) {
    http_response_code(403);
    echo "NOT LOGGED IN";
    exit;
}

if (!isset($_POST['post_id'])) {
    echo "NO POST ID";
    exit;
}

$post_id = intval($_POST['post_id']);
$user_id = current_user_id();

$stmt = $mysqli->prepare("DELETE FROM posts WHERE post_id = ? AND user_id = ?");

if (!$stmt) {
    echo "SQL ERROR: " . $mysqli->error;
    exit;
}

$stmt->bind_param("ii", $post_id, $user_id);

if ($stmt->execute()) {
    echo "OK";
} else {
    echo "EXEC ERROR: " . $stmt->error;
}
