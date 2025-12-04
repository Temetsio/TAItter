<?php
require_once 'config.php';

$hashtag = $_GET['hashtag'] ?? null;
$currentUserId = current_user_id();

if ($hashtag) {
    $sql = "
        SELECT 
            p.post_id,
            p.content,
            p.created_at,
            p.edited_at,
            p.user_id,
            u.username,
            u.profile_picture_url,
            NULL AS reposted_by,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.post_id) AS like_count,
            EXISTS(SELECT 1 FROM likes WHERE post_id = p.post_id AND user_id = ?) AS user_has_liked
        FROM posts p
        JOIN users u ON p.user_id = u.user_id
        JOIN post_hashtags ph ON ph.post_id = p.post_id
        JOIN hashtags h ON h.hashtag_id = ph.hashtag_id
        WHERE h.tag_name = ?
        ORDER BY p.created_at DESC
        LIMIT 100
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("is", $currentUserId, $hashtag);
} else {
    $sql = "
        (
            SELECT 
                p.post_id,
                p.content,
                p.created_at,
                p.edited_at,
                p.user_id,
                u.username,
                u.profile_picture_url,
                NULL AS reposted_by,
                (SELECT COUNT(*) FROM likes WHERE post_id = p.post_id) AS like_count,
                EXISTS(SELECT 1 FROM likes WHERE post_id = p.post_id AND user_id = ?) AS user_has_liked
            FROM posts p
            JOIN users u ON p.user_id = u.user_id
        )
        UNION ALL
        (
            SELECT 
                p.post_id,
                p.content,
                r.created_at,
                p.edited_at,
                p.user_id,
                u2.username,
                u2.profile_picture_url,
                u.username AS reposted_by,
                (SELECT COUNT(*) FROM likes WHERE post_id = p.post_id) AS like_count,
                EXISTS(SELECT 1 FROM likes WHERE post_id = p.post_id AND user_id = ?) AS user_has_liked
            FROM reposts r
            JOIN posts p ON r.post_id = p.post_id
            JOIN users u ON r.user_id = u.user_id
            JOIN users u2 ON p.user_id = u2.user_id
        )
        ORDER BY created_at DESC
        LIMIT 100
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ii", $currentUserId, $currentUserId);
}

$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {

    $content = htmlspecialchars($row['content']);
    $content = preg_replace('/#([A-Za-z0-9_åäöÅÄÖ\-]+)/u', '<a href="index.php?hashtag=$1">#$1</a>', $content);
    $content = preg_replace('/@([A-Za-z0-9_]+)/', '<a href="profile.php?u=$1">@$1</a>', $content);

    $isOwnPost = ($row['user_id'] == $currentUserId);
    $editedLabel = $row['edited_at'] ? '<small style="color:#999;margin-left:6px;">(muokattu)</small>' : '';

    $likeIcon = $row['user_has_liked'] ? '❤️' : '🤍';
    $likeText = $row['user_has_liked'] ? 'Unlike' : 'Like';
    $likeCount = $row['like_count'];

    // comment_count oletetaan nyt mukana rivissä
    $commentCount = isset($row['comment_count']) ? (int)$row['comment_count'] : 0;

    echo "<div class='card' id='post-{$row['post_id']}'>";

    if ($row['reposted_by']) {
        echo "<div style='font-size:12px;color:#555;'>🔁 ".htmlspecialchars($row['reposted_by'])." reposted</div>";
    }

    echo "
      <div class='card-header'>
        <strong><a href='profile.php?u={$row['username']}'>".htmlspecialchars($row['username'])."</a></strong>
        <small>{$row['created_at']}</small> {$editedLabel}
      </div>
      <div class='post-content-{$row['post_id']}'>$content</div>
    ";

    if ($isOwnPost) {
        $rawContent = htmlspecialchars(addslashes($row['content']));
        echo "
        <div style='margin-top:8px;'>
            <button onclick=\"editPost({$row['post_id']}, '{$rawContent}')\">Muokkaa</button>
            <button onclick=\"deletePost({$row['post_id']})\" style='color:red;margin-left:6px;'>Poista</button>
        </div>";
    }

    echo "
      <div class='card-actions'>
        <button onclick='toggleLike({$row['post_id']}, this)' id='like-btn-{$row['post_id']}'>
          <span id='like-icon-{$row['post_id']}'>{$likeIcon}</span>
          <span id='like-text-{$row['post_id']}'>{$likeText}</span>
          (<span id='like-count-{$row['post_id']}'>{$likeCount}</span>)
        </button>

        <button type='button' onclick='openComments({$row['post_id']})' id='comment-btn-{$row['post_id']}'>
          💬 <span id='comment-count-{$row['post_id']}'>{$commentCount}</span>
        </button>

        <form method='post' action='repost.php' style='display:inline;'>
          <input type='hidden' name='post_id' value='{$row['post_id']}'>
          <button>Repost</button>
        </form>
      </div>

      <div class='comment-panel' id='comment-panel-{$row['post_id']}' style='display:none;margin-top:8px;'>
        <div id='comment-list-{$row['post_id']}' style='margin-bottom:8px;'></div>
        <div>
          <input type='text' id='comment-input-{$row['post_id']}' placeholder='Kirjoita kommentti' maxlength='144' style='width:75%'>
          <button type='button' onclick='postComment({$row['post_id']})'>Lähetä</button>
        </div>
      </div>

    </div>";
}