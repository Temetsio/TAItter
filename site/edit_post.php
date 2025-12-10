<?php
require_once 'config.php';

if (!current_user_id()) {
    http_response_code(401);
    exit;
}

$postId = $_POST['post_id'] ?? null;
$content = $_POST['content'] ?? null;

if (!$postId || !$content) {
    http_response_code(400);
    exit;
}

$stmt = $mysqli->prepare("SELECT user_id FROM posts WHERE post_id = ?");
$stmt->bind_param("i", $postId);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post || $post['user_id'] != current_user_id()) {
    http_response_code(403);
    exit;
}

$stmt = $mysqli->prepare("UPDATE posts SET content = ?, edited_at = NOW() WHERE post_id = ?");
$stmt->bind_param("si", $content, $postId);
$stmt->execute();


$mysqli->query("DELETE FROM post_hashtags WHERE post_id = $postId");

preg_match_all('/#(\w+)/', $content, $matches);
if (!empty($matches[1])) {
    foreach ($matches[1] as $tag) {
        $stmt = $mysqli->prepare("INSERT IGNORE INTO hashtags (tag_name) VALUES (?)");
        $stmt->bind_param("s", $tag);
        $stmt->execute();
        
        $stmt = $mysqli->prepare("SELECT hashtag_id FROM hashtags WHERE tag_name = ?");
        $stmt->bind_param("s", $tag);
        $stmt->execute();
        $tagId = $stmt->get_result()->fetch_assoc()['hashtag_id'];
        
        $stmt = $mysqli->prepare("INSERT IGNORE INTO post_hashtags (post_id, hashtag_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $postId, $tagId);
        $stmt->execute();
    }
}

echo "OK";