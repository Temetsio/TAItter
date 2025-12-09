<?php
require_once 'config.php';
if (!current_user_id()) exit;

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$ids = $data['post_ids'] ?? [];

if (!is_array($ids) || empty($ids)) {
    echo json_encode(['success'=>true,'counts'=>[]]);
    exit;
}

$ids = array_filter($ids, fn($x)=>ctype_digit((string)$x));
if (!$ids) {
    echo json_encode(['success'=>true,'counts'=>[]]);
    exit;
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));

$sql = "SELECT post_id, COUNT(*) cnt FROM comments WHERE post_id IN ($placeholders) GROUP BY post_id";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$ids);
$stmt->execute();

$res = $stmt->get_result();
$counts = [];

while ($row = $res->fetch_assoc()) {
    $counts[$row['post_id']] = (int)$row['cnt'];
}

foreach ($ids as $id) {
    if (!isset($counts[$id])) $counts[$id] = 0;
}

echo json_encode(['success'=>true,'counts'=>$counts]);
