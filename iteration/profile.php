<?php
require_once 'config.php';

$user = $_GET['u'] ?? null;
if (!$user) {
    header('Location: index.php');
    exit;
}

$stmt = $mysqli->prepare("SELECT user_id, username, bio, profile_picture_url, created_at FROM users WHERE username = ?");
$stmt->bind_param('s', $user);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
if (!$row) {
    echo "User not found";
    exit;
}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($row['username']) ?></title>
</head>
<body>

<div style="max-width:800px;margin:20px auto;">

<a href="index.php">Home</a> | <a href="logout.php">Logout</a>

<h2><?= htmlspecialchars($row['username']) ?></h2>

<?php if (!empty($row['profile_picture_url'])): ?>
  <img src="<?= htmlspecialchars($row['profile_picture_url']) ?>" 
       alt="Profile picture"
       style="width:120px;height:120px;object-fit:cover;border-radius:50%">
<?php endif; ?>


<p><?= nl2br(htmlspecialchars($row['bio'])) ?></p>

<?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']): ?>
  <p><a href="edit_profile.php">Edit profile</a></p>
<?php endif; ?>

<hr>

<h3>Recent posts</h3>

<?php

$stmt = $mysqli->prepare("SELECT post_id, content, created_at FROM posts WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
$stmt->bind_param('i', $row['user_id']);
$stmt->execute();
$r = $stmt->get_result();

while ($p = $r->fetch_assoc()) {

    $c = htmlspecialchars($p['content']);

  
    $c = preg_replace('/#([A-Za-z0-9_åäöÅÄÖ\-]+)/u',
        '<a href="index.php?hashtag=$1">#$1</a>', $c);

    $c = preg_replace('/@([A-Za-z0-9_]+)/',
        '<a href="profile.php?u=$1">@$1</a>', $c);

    echo "
    <div style='border:1px solid #ccc;padding:10px;margin-bottom:10px'>
        <small>{$p['created_at']}</small><br>
        $c
    </div>";
}
?>

</div>
</body>
</html>

