
<?php
require_once 'config.php';

$uid = current_user_id();
if (!$uid) exit;

$stmt = $mysqli->prepare("
    SELECT COUNT(*) AS cnt
    FROM likes l
    JOIN posts p ON l.post_id = p.post_id
    WHERE p.user_id = ?
    AND l.created_at > IFNULL(
        (SELECT last_seen_likes FROM users WHERE user_id = ?),
        '1970-01-01'
    )
");
$stmt->bind_param("ii", $uid, $uid);
$stmt->execute();
$unseenCount = $stmt->get_result()->fetch_assoc()['cnt'];

$stmt2 = $mysqli->prepare("
    SELECT u.username AS username, p.content AS content, l.created_at AS created_at
    FROM likes l
    JOIN posts p ON l.post_id = p.post_id
    JOIN users u ON l.user_id = u.user_id
    WHERE p.user_id = ?
    AND l.created_at > IFNULL(
        (SELECT last_seen_likes FROM users WHERE user_id = ?),
        '1970-01-01'
    )
    ORDER BY l.created_at DESC
    LIMIT 10
");
$stmt2->bind_param("ii", $uid, $uid);
$stmt2->execute();
$r = $stmt2->get_result();

$likesList = "";
if ($r->num_rows === 0) {
    $likesList = '<div style="padding:12px;color:#999;font-size:13px;">Ei uusia tykkäyksiä</div>';
} else {
    while ($row = $r->fetch_assoc()) {
        $likesList .= "<div class='dd-item'>\n"
            . "        ❤️ <b>" . htmlspecialchars($row['username']) . "</b> liked:<br>\n"
            . "        " . htmlspecialchars($row['content']) . "\n"
            . "        <small>" . $row['created_at'] . "</small>\n"
            . "    </div>";
    }
}

if (isset($_GET['json']) && $_GET['json']) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true, 
        'html' => $likesList, 
        'count' => $unseenCount
    ]);
    exit;
}

echo $likesList;
