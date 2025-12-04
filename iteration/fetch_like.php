<?php
require_once 'config.php';

$uid = current_user_id();

$stmt = $mysqli->prepare("
    SELECT u.username AS username, p.content AS content, l.created_at AS created_at
    FROM likes l
    JOIN posts p ON l.post_id = p.post_id
    JOIN users u ON l.user_id = u.user_id
    WHERE p.user_id = ?
    ORDER BY l.created_at DESC
    LIMIT 1000
");
$stmt->bind_param("i", $uid);
$stmt->execute();
$r = $stmt->get_result();

$likesList = "";
$count = 0;
while ($row = $r->fetch_assoc()) {
    $likesList .= "<div class='dd-item'>\n"
        . "        ❤️ <b>" . htmlspecialchars($row['username']) . "</b> liked:<br>\n"
        . "        " . htmlspecialchars($row['content']) . "<br>\n"
        . "        <small>" . $row['created_at'] . "</small>\n"
        . "    </div>";
    $count++;
}

if (isset($_GET['json']) && $_GET['json']) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'html' => $likesList, 'count' => $count]);
    exit;
}

echo $likesList;
