<?php
require_once 'config.php';
if (!current_user_id()) exit;

header('Content-Type: application/json');

$uid = current_user_id();

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
$count = $stmt->get_result()->fetch_assoc()['cnt'];

$stmt2 = $mysqli->prepare("
    SELECT u.username, p.content, l.created_at
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

$html = '';
if ($r->num_rows === 0) {
    $html = '<div style="padding:12px;color:#999;font-size:13px;">Ei uusia tykkäyksiä</div>';
} else {
    while ($row = $r->fetch_assoc()) {
        $html .= "<div class='dd-item'>
                ❤️ <b>".htmlspecialchars($row['username'])."</b> liked:<br>"
                .htmlspecialchars($row['content']).
                "<small>{$row['created_at']}</small>
            </div>";
    }
}

echo json_encode([
    'success' => true,
    'count' => $count,
    'html' => $html
]);