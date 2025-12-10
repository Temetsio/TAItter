<?php
require_once 'config.php';

if (!current_user_id()) {
    header('Location: login.php');
    exit;
}

$hashtag = $_POST['hashtag'] ?? $_GET['hashtag'] ?? null;
$action = $_POST['action'] ?? $_GET['action'] ?? 'follow';

if (!$hashtag) {
    header('Location: index.php');
    exit;
}

$userId = current_user_id();
$stmt = $mysqli->prepare("INSERT IGNORE INTO hashtags (tag_name) VALUES (?)");
$stmt->bind_param("s", $hashtag);
$stmt->execute();

$stmt = $mysqli->prepare("SELECT hashtag_id FROM hashtags WHERE tag_name = ?");
$stmt->bind_param("s", $hashtag);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$hashtagId = $result->fetch_assoc()['hashtag_id'];

if ($action === 'follow') {
    $stmt = $mysqli->prepare("INSERT IGNORE INTO followed_hashtags (user_id, hashtag_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $userId, $hashtagId);
    $stmt->execute();
} else {
    $stmt = $mysqli->prepare("DELETE FROM followed_hashtags WHERE user_id = ? AND hashtag_id = ?");
    $stmt->bind_param("ii", $userId, $hashtagId);
    $stmt->execute();
}
header('Location: index.php?hashtag=' . urlencode($hashtag));
exit;