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

$isFollowing = false;
$viewerIsOwner = false;

if (current_user_id()) {
    $viewerIsOwner = (current_user_id() == $row['user_id']);
    
    if (!$viewerIsOwner) {
        $stmt = $mysqli->prepare("
            SELECT COUNT(*) as is_following 
            FROM follows 
            WHERE follower_id = ? AND following_id = ?
        ");
        $currentUserId = current_user_id();
        $profileUserId = $row['user_id'];
        $stmt->bind_param("ii", $currentUserId, $profileUserId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $isFollowing = $result['is_following'] > 0;
    }
}

$profileUserId = $row['user_id'];

$stmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM follows WHERE following_id = ?");
$stmt->bind_param("i", $profileUserId);
$stmt->execute();
$followersCount = $stmt->get_result()->fetch_assoc()['cnt'];

$stmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM follows WHERE follower_id = ?");
$stmt->bind_param("i", $profileUserId);
$stmt->execute();
$followingCount = $stmt->get_result()->fetch_assoc()['cnt'];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($row['username']) ?> – TAltter Profile</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
:root {
    --accent: #ff6fb5;
    --accent-dark: #ff4fa5;
    --accent-soft: #ffe5f3;
    --bg-card: rgba(255, 255, 255, 0.95);
    --input-border: #ffd4ec;
    --text-main: #1f1033;
    --text-soft: #5c4969;
    --chip-bg: #fce4ff;
    --chip-text: #6f2f8f;
}

*,
*::before,
*::after {
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    font-family: "Poppins", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 40%, #fbc2eb 100%);
    padding: 20px 12px 28px;
    color: var(--text-main);
}

.app-shell {
    max-width: 900px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.card {
    background: var(--bg-card);
    backdrop-filter: blur(18px);
    border-radius: 18px;
    padding: 16px 18px;
    box-shadow: 0 16px 35px rgba(255, 105, 180, 0.25);
    position: relative;
    overflow: hidden;
}

.card::before {
    content: "";
    position: absolute;
    inset: -40px;
    background:
        radial-gradient(circle at top left, rgba(255,255,255,0.6), transparent 55%),
        radial-gradient(circle at bottom right, rgba(255,255,255,0.5), transparent 55%);
    opacity: 0.5;
    pointer-events: none;
}

.card-inner {
    position: relative;
    z-index: 1;
}

.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.logo-badge {
    width: 40px;
    height: 40px;
    border-radius: 14px;
    background: linear-gradient(135deg, #ff6fb5, #ffc75f);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.app-title {
    display: flex;
    flex-direction: column;
}

.app-title span:first-child {
    font-weight: 600;
    font-size: 18px;
}

.app-title span:last-child {
    font-size: 12px;
    color: var(--text-soft);
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

a {
    color: var(--accent-dark);
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

.btn-outline {
    border-radius: 999px;
    border: 1.5px solid var(--accent-soft);
    background: rgba(255,255,255,0.9);
    padding: 7px 14px;
    font: inherit;
    font-size: 13px;
    cursor: pointer;
    color: var(--text-soft);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background 0.12s, border-color 0.12s, transform 0.08s;
}

.btn-outline:hover {
    background: var(--accent-soft);
    border-color: var(--accent);
    transform: translateY(-1px);
}

.btn-primary {
    border-radius: 999px;
    border: none;
    background: linear-gradient(135deg, var(--accent), var(--accent-dark));
    padding: 8px 16px;
    font: inherit;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    color: #fff;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: transform 0.08s, box-shadow 0.12s;
    box-shadow: 0 8px 20px rgba(255, 111, 181, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(255, 111, 181, 0.4);
}

.btn-secondary {
    border-radius: 999px;
    border: 1.5px solid var(--accent);
    background: rgba(255,255,255,0.9);
    padding: 7px 14px;
    font: inherit;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    color: var(--accent-dark);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background 0.12s, transform 0.08s;
}

.btn-secondary:hover {
    background: var(--accent-soft);
    transform: translateY(-1px);
}

.profile-header {
    display: flex;
    gap: 18px;
    align-items: flex-start;
    flex-wrap: wrap;
}

.profile-avatar {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ff6fb5, #ffc75f);
    position: relative;
    flex-shrink: 0;
    overflow: hidden;
    box-shadow: 0 12px 30px rgba(255, 111, 181, 0.4);
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-avatar-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 44px;
    font-weight: 700;
}

.profile-main {
    flex: 1;
    min-width: 0;
}

.profile-username {
    margin: 0;
    font-size: 22px;
    font-weight: 600;
}

.profile-handle {
    margin: 4px 0 8px;
    font-size: 14px;
    color: var(--text-soft);
}

.profile-stats {
    display: flex;
    gap: 16px;
    margin: 8px 0;
    font-size: 14px;
}

.profile-stat {
    display: flex;
    gap: 4px;
}

.profile-stat-link {
    display: flex;
    gap: 4px;
    align-items: baseline;
    text-decoration: none;
    color: inherit;
    cursor: pointer;
}

.profile-stat-link:hover .profile-stat-label {
    text-decoration: underline;
}


.profile-stat-number {
    font-weight: 600;
    color: var(--text-main);
}

.profile-stat-label {
    color: var(--text-soft);
}

.profile-meta {
    font-size: 12px;
    color: var(--text-soft);
    margin-top: 4px;
}

.profile-bio {
    margin-top: 10px;
    font-size: 14px;
    white-space: pre-wrap;
}

.profile-actions {
    margin-top: 12px;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.section-title {
    margin: 0 0 10px;
    font-size: 18px;
    font-weight: 600;
}

.post-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.post-card {
    border-radius: 14px;
    padding: 10px 12px;
    background: #ffffff;
    box-shadow: 0 10px 25px rgba(255, 105, 180, 0.12);
    font-size: 14px;
}

.post-meta {
    font-size: 11px;
    color: var(--text-soft);
    margin-bottom: 6px;
}

.post-body a {
    color: var(--accent-dark);
    font-weight: 500;
}

@media (max-width: 640px) {
    .topbar {
        flex-direction: column;
        align-items: flex-start;
    }

    .topbar-right {
        width: 100%;
        justify-content: flex-start;
    }
}
</style>
</head>
<body>
<div class="app-shell">

    <div class="card">
        <div class="card-inner topbar">
            <div class="topbar-left">
                <div class="logo-badge">T</div>
                <div class="app-title">
                    <span>TAltter</span>
                    <span>Profile</span>
                </div>
            </div>
            <div class="topbar-right">
                <a href="index.php" class="btn-outline">← Back to feed</a>
                <?php if (current_user_id()): ?>
                    <a href="logout.php" class="btn-outline">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn-outline">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <div class="card">
        <div class="card-inner profile-header">
            <div class="profile-avatar">
                <?php if (!empty($row['profile_picture_url'])): ?>
                    <img src="<?= htmlspecialchars($row['profile_picture_url']) ?>" alt="Profile picture">
                <?php else: ?>
                    <?php 
                    $firstChar = substr($row['username'], 0, 1);
                    ?>
                    <div class="profile-avatar-fallback">
                        <?= strtoupper(htmlspecialchars($firstChar)) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="profile-main">
                <h1 class="profile-username"><?= htmlspecialchars($row['username']) ?></h1>
                <div class="profile-handle">@<?= htmlspecialchars($row['username']) ?></div>
                
<div class="profile-stats">
    <a class="profile-stat-link" href="followers.php?u=<?= urlencode($row['username']) ?>">
        <span class="profile-stat-number"><?= $followersCount ?></span>
        <span class="profile-stat-label">Likers</span>
    </a>
    <a class="profile-stat-link" href="following.php?u=<?= urlencode($row['username']) ?>">
        <span class="profile-stat-number"><?= $followingCount ?></span>
        <span class="profile-stat-label">Liked</span>
    </a>
</div>

                
                <div class="profile-meta">
                    Joined <?= htmlspecialchars(date('F j, Y', strtotime($row['created_at']))) ?>
                </div>

                <?php if (!empty($row['bio'])): ?>
                    <div class="profile-bio">
                        <?= nl2br(htmlspecialchars($row['bio'])) ?>
                    </div>
                <?php endif; ?>

                <div class="profile-actions">
                    <?php if ($viewerIsOwner): ?>
                        <a href="edit_profile.php" class="btn-outline">Edit profile</a>
                    <?php elseif (current_user_id()): ?>
                        <?php if ($isFollowing): ?>
                            <form method="post" action="follow.php" style="display:inline;">
                                <input type="hidden" name="username" value="<?= htmlspecialchars($row['username']) ?>">
                                <input type="hidden" name="action" value="unfollow">
                                <button type="submit" class="btn-secondary">Unlike</button>
                            </form>
                        <?php else: ?>
                            <form method="post" action="follow.php" style="display:inline;">
                                <input type="hidden" name="username" value="<?= htmlspecialchars($row['username']) ?>">
                                <input type="hidden" name="action" value="follow">
                                <button type="submit" class="btn-primary">Like</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-inner">
            <h2 class="section-title">Recent posts</h2>
            <div class="post-list">
                <?php
                $stmt = $mysqli->prepare("
                    SELECT 
                    post_id, 
                    content, 
                    DATE_FORMAT(created_at, '%d.%m.%Y %H:%i') AS created_at
                    FROM posts 
                    WHERE user_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT 50
                    ");

                $stmt->bind_param('i', $row['user_id']);
                $stmt->execute();
                $r = $stmt->get_result();

                while ($p = $r->fetch_assoc()) {
                    $c = htmlspecialchars($p['content']);

                    $c = preg_replace(
                        '/#([A-Za-z0-9_åäöÅÄÖ\-]+)/u',
                        '<a href="index.php?hashtag=$1">#$1</a>',
                        $c
                    );

                    $c = preg_replace(
                        '/@([A-Za-z0-9_]+)/',
                        '<a href="profile.php?u=$1">@$1</a>',
                        $c
                    );

                    echo "<div class='post-card'>
                            <div class='post-meta'>".$p['created_at']."</div>
                            <div class='post-body'>$c</div>
                        </div>";
                }
                ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>