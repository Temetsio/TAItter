<?php
require_once 'config.php';

$hashtag = $_GET['hashtag'] ?? null;
$params = [];
$sql = "SELECT p.post_id, p.content, p.created_at, u.user_id, u.username, u.profile_picture_url
        FROM posts p
        JOIN users u ON p.user_id = u.user_id";

if ($hashtag) {
    $sql .= " JOIN post_hashtags ph ON ph.post_id = p.post_id
              JOIN hashtags h ON h.hashtag_id = ph.hashtag_id
              WHERE h.tag_name = ?";
    $params[] = $hashtag;
}
$sql .= " ORDER BY p.created_at DESC LIMIT 100";

if ($params) {
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $params[0]);
} else {
    $stmt = $mysqli->prepare($sql);
}
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $u = htmlspecialchars($row['username']);
    $content = htmlspecialchars($row['content']);
    $content = preg_replace('/#([A-Za-z0-9_åäöÅÄÖ\-]+)/u', '<a href="index.php?hashtag=$1">#$1</a>', $content);
    $content = preg_replace('/@([A-Za-z0-9_]+)/', '<a href="profile.php?u=$1">@$1</a>', $content);

    echo "<div class='card'>
            <div class='card-header'><strong><a href='profile.php?u={$u}'>$u</a></strong> <small>{$row['created_at']}</small></div>
            <div class='card-body'>{$content}</div>
            <div class='card-actions'>
                <form method='post' action='repost.php' style='display:inline;'><input type='hidden' name='post_id' value='{$row['post_id']}'><button>Repost</button></form>
            </div>
          </div>";
}
