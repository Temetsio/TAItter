<?php
require_once 'config.php';
if (!current_user_id()) { http_response_code(403); echo "Login required"; exit; }

$content = trim($_POST['content'] ?? '');
if ($content === '') { header('Location: index.php'); exit; }

$mysqli->begin_transaction();
try {
    $stmt = $mysqli->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
    $uid = current_user_id();
    $stmt->bind_param('is', $uid, $content);
    $stmt->execute();
    $post_id = $stmt->insert_id;

    preg_match_all('/#([A-Za-z0-9_åäöÅÄÖ\-]+)/u', $content, $m);
    $tags = array_unique($m[1] ?? []);
    $insertHashtagStmt = $mysqli->prepare("INSERT INTO hashtags (tag_name) VALUES (?)");
    $selectHashtagStmt = $mysqli->prepare("SELECT hashtag_id FROM hashtags WHERE tag_name = ?");
    $insertPostHashtag = $mysqli->prepare("INSERT IGNORE INTO post_hashtags (post_id, hashtag_id) VALUES (?, ?)");

    foreach ($tags as $tag) {
        $t = mb_strtolower($tag, 'UTF-8');
        $selectHashtagStmt->bind_param('s', $t);
        $selectHashtagStmt->execute();
        $res = $selectHashtagStmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $hid = $row['hashtag_id'];
        } else {
            $insertHashtagStmt->bind_param('s', $t);
            $insertHashtagStmt->execute();
            $hid = $insertHashtagStmt->insert_id;
        }
        $insertPostHashtag->bind_param('ii', $post_id, $hid);
        $insertPostHashtag->execute();
    }

    preg_match_all('/@([A-Za-z0-9_]+)/', $content, $mm);
    $mentioned = array_unique($mm[1] ?? []);
    $selectUser = $mysqli->prepare("SELECT user_id FROM users WHERE username = ?");
    $insertMention = $mysqli->prepare("INSERT INTO mentions (post_id, mentioned_user_id) VALUES (?, ?)");
    foreach ($mentioned as $username) {
        $selectUser->bind_param('s', $username);
        $selectUser->execute();
        $r = $selectUser->get_result();
        if ($ru = $r->fetch_assoc()) {
            $mid = $ru['user_id'];
            $insertMention->bind_param('ii', $post_id, $mid);
            $insertMention->execute();
        }
    }

    $mysqli->commit();
    header('Location: index.php');
    exit;
} catch (Exception $e) {
    $mysqli->rollback();
    error_log("Post error: " . $e->getMessage());
    header('Location: index.php');
    exit;
}
?>
