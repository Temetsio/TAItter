<?php
require_once 'config.php';

$user = $_GET['u'] ?? null;
if (!$user) {
    header('Location: index.php');
    exit;
}

$stmt = $mysqli->prepare("SELECT user_id, username FROM users WHERE username = ?");
$stmt->bind_param('s', $user);
$stmt->execute();
$res = $stmt->get_result();
$profile = $res->fetch_assoc();
if (!$profile) {
    echo "User not found";
    exit;
}
$profileUserId = $profile['user_id'];

$stmt = $mysqli->prepare("
    SELECT u.user_id, u.username, u.bio, u.profile_picture_url
    FROM follows f
    JOIN users u ON f.follower_id = u.user_id
    WHERE f.following_id = ?
    ORDER BY u.username ASC
");
$stmt->bind_param('i', $profileUserId);
$stmt->execute();
$followers = $stmt->get_result();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($profile['username']) ?> – Followers</title>
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
    --text-main: #1f1033;
    --text-soft: #5c4969;
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
    border-radius: 18px;
    padding: 16px 18px;
    position: relative;
}

.card-inner { 
    position: relative; 
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
}

.section-title {
    margin: 0 0 10px;
    font-size: 18px;
    font-weight: 600;
}

.user-list { 
    display: flex; 
    flex-direction: column; 
    gap: 10px; 
}

.user-row {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 14px;
    background: #fff;
    font-size: 14px;
}

.user-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ff6fb5, #ffc75f);
    overflow: hidden;
    flex-shrink: 0;
}

.user-avatar img { 
    width: 100%; 
    height: 100%; 
    object-fit: cover; 
}

.user-avatar-fallback {
    width: 100%; 
    height: 100%;
    display: flex; 
    align-items: center; 
    justify-content: center;
    color: #fff; 
    font-weight: 600;
}

.user-main { 
    flex: 1; 
    min-width: 0; 
}

.user-name { 
    font-weight: 600; 
    margin-bottom: 2px; 
}

.user-handle { 
    font-size: 12px; 
    color: var(--text-soft); 
    margin-bottom: 4px; 
}

.user-bio { 
    font-size: 13px; 
    color: var(--text-main); 
    white-space: pre-wrap; 
}

.empty-text { 
    font-size: 14px; 
    color: var(--text-soft); 
}

@media (max-width: 640px) {
    .topbar { 
        flex-direction: column; 
        align-items: flex-start; 
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
                    <span>Likers of @<?= htmlspecialchars($profile['username']) ?></span>
                </div>
            </div>
            <div class="topbar-right">
                <a href="profile.php?u=<?= urlencode($profile['username']) ?>" class="btn-outline">← Back to profile</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-inner">
            <h2 class="section-title">
                Likers (<?= $followers->num_rows ?>)
            </h2>

            <?php if ($followers->num_rows === 0): ?>
                <p class="empty-text">This user has no followers yet.</p>
            <?php else: ?>
                <div class="user-list">
                    <?php while ($u = $followers->fetch_assoc()): ?>
                        <a class="user-row" href="profile.php?u=<?= urlencode($u['username']) ?>">
                            <div class="user-avatar">
                                <?php if (!empty($u['profile_picture_url'])): ?>
                                    <img src="<?= htmlspecialchars($u['profile_picture_url']) ?>" alt="">
                                <?php else: ?>
                                    <div class="user-avatar-fallback">
                                        <?= strtoupper(htmlspecialchars(substr($u['username'], 0, 1))) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="user-main">
                                <div class="user-name"><?= htmlspecialchars($u['username']) ?></div>
                                <div class="user-handle">@<?= htmlspecialchars($u['username']) ?></div>
                                <?php if (!empty($u['bio'])): ?>
                                    <div class="user-bio"><?= nl2br(htmlspecialchars($u['bio'])) ?></div>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>
</body>
</html>
