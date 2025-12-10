<?php
require_once 'config.php';

if (!current_user_id()) {
    header('Location: login.php');
    exit;
}

$username = $_POST['username'] ?? null;
$action = $_POST['action'] ?? 'follow';

if (!$username) {
    header('Location: index.php');
    exit;
}

$stmt = $mysqli->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$followingId = $result->fetch_assoc()['user_id'];
$followerId = current_user_id();

if ($followerId === $followingId) {
    header('Location: profile.php?u=' . urlencode($username));
    exit;
}

if ($action === 'follow') {
    $stmt = $mysqli->prepare("INSERT IGNORE INTO follows (follower_id, following_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $followerId, $followingId);
    $stmt->execute();
} else {
    $stmt = $mysqli->prepare("DELETE FROM follows WHERE follower_id = ? AND following_id = ?");
    $stmt->bind_param("ii", $followerId, $followingId);
    $stmt->execute();
}

header('Location: profile.php?u=' . urlencode($username));
exit;