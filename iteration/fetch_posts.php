<?php
require_once 'config.php';

$hashtag = $_GET['hashtag'] ?? null;

if ($hashtag) {
    $sql = "
        SELECT 
            p.post_id,
            p.content,
            p.created_at,
            u.username,
            u.profile_picture_url,
            NULL AS reposted_by
        FROM posts p
        JOIN users u ON p.user_id = u.user_id
        JOIN post_hashtags ph ON ph.post_id = p.post_id
        JOIN hashtags h ON h.hashtag_id = ph.hashtag_id
        WHERE h.tag_name = ?
        ORDER BY p.created_at DESC
        LIMIT 100
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $hashtag);
} else {
    $sql = "
        (
            SELECT 
                p.post_id,
                p.content,
                p.created_at,
                u.username,
                u.profile_picture_url,
                NULL AS reposted_by
            FROM posts p
            JOIN users u ON p.user_id = u.user_id
        )
        UNION ALL
        (
            SELECT 
                p.post_id,
                p.content,
                r.created_at,
                u2.username AS username,
                u2.profile_picture_url,
                u.username AS reposted_by
            FROM reposts r
            JOIN posts p ON r.post_id = p.post_id
            JOIN users u ON r.user_id = u.user_id      -- who reposted
            JOIN users u2 ON p.user_id = u2.user_id    -- original author
        )
        ORDER BY created_at DESC
        LIMIT 100
    ";
    $stmt = $mysqli->prepare($sql);
}

$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {

    $content = htmlspecialchars($row['content']);
    $content = preg_replace('/#([A-Za-z0-9_åäöÅÄÖ\-]+)/u', '<a href="index.php?hashtag=$1">#$1</a>', $content);
    $content = preg_replace('/@([A-Za-z0-9_]+)/', '<a href="profile.php?u=$1">@$1</a>', $content);

    echo "<div class='card'>";

    if ($row['reposted_by']) {
        echo "<div style='font-size:12px;color:#555;'>🔁 " . 
             htmlspecialchars($row['reposted_by']) . " reposted</div>";
    }

    echo "
        <div class='card-header'>
            <strong><a href='profile.php?u={$row['username']}'>"
            . htmlspecialchars($row['username']) .
            "</a></strong>
            <small>{$row['created_at']}</small>
        </div>
        <div class='card-body'>$content</div>
        <div class='card-actions'>
            <form method='post' action='repost.php' style='display:inline;'>
                <input type='hidden' name='post_id' value='{$row['post_id']}'>
                <button>Repost</button>
            </form>
        </div>
    </div>";
}
