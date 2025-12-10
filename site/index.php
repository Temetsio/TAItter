<?php
require_once 'config.php';
if (!current_user_id()) { header('Location: login.php'); exit; }

$filterHashtag = $_GET['hashtag'] ?? null;
$filterUser = $_GET['user'] ?? null;
$uid = current_user_id();
$like = '%@'.current_username().'%';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>TAltter – Timeline</title>
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
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 16px;
}


/* Card */
.card {
    background: var(--bg-card);
    backdrop-filter: blur(18px);
    border-radius: 18px;
    padding: 16px 18px;
    box-shadow: none;   
    position: relative;
    overflow: visible;
}

/* Make the topbar card sit above the other cards */
.app-shell > .card:first-of-type {
    position: relative;
    z-index: 50;
}



.card::before {
    content: "";
    position: absolute;
    inset: -40px;
    opacity: 0.5;
    pointer-events: none;
}

.card-inner {
    position: relative;
    z-index: 1;
}

/* Top bar */
.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    position: relative;
    z-index: 50;
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

.logged-in-as {
    font-size: 13px;
    color: var(--text-soft);
}

.logged-in-as strong {
    color: var(--accent-dark);
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

/* Links & buttons */
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

.btn-pill {
    border-radius: 999px;
    border: none;
    padding: 8px 14px;
    font: inherit;
    font-size: 13px;
    font-weight: 500;
    background: linear-gradient(135deg, var(--accent), var(--accent-dark));
    color: #fff;
    cursor: pointer;
    box-shadow: 0 10px 22px rgba(255, 111, 181, 0.45);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: transform 0.08s ease, box-shadow 0.12s ease, filter 0.12s ease;
}

.btn-pill:hover {
    filter: brightness(1.05);
    transform: translateY(-1px);
    box-shadow: 0 12px 26px rgba(255, 111, 181, 0.5);
}

.btn-pill:active {
    transform: translateY(1px);
    box-shadow: 0 8px 18px rgba(255, 111, 181, 0.4);
}

/* Badge */
.badge {
    min-width: 18px;
    height: 18px;
    border-radius: 999px;
    background: #ffeff8;
    color: #b0003a;
    font-size: 11px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 6px;
}

/* Layout grid */
.layout-grid {
    display: grid;
    grid-template-columns: 260px minmax(0, 1fr) 260px;
    gap: 18px;
    align-items: flex-start;
}

.left-column,
.right-column {
    position: sticky;
    top: 20px;
}


.left-column .card + .card,
.right-column .card + .card {
    margin-top: 16px;
}

main .card + .card {
    margin-top: 16px;
}

/* Sidebar */
.sidebar-heading {
    margin: 0 0 8px;
    font-size: 16px;
    font-weight: 600;
}

.sidebar-sub {
    margin: 0 0 10px;
    font-size: 13px;
    color: var(--text-soft);
}

.profile-handle {
    font-size: 14px;
    font-weight: 600;
}

.sidebar-links {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-top: 6px;
}

.sidebar-links a {
    font-size: 13px;
}

/* Hashtag chips */
.tag-chip {
    display: inline-flex;
    align-items: center;
    padding: 4px 9px;
    border-radius: 999px;
    background: var(--chip-bg);
    color: var(--chip-text);
    font-size: 12px;
    margin: 3px 4px 3px 0;
    transition: background 0.12s, transform 0.08s;
}

.tag-chip:hover {
    background: #f1ceff;
    transform: translateY(-1px);
    text-decoration: none;
}

/* Post composer */
.post-composer {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.post-composer textarea {
    width: 100%;
    min-height: 80px;
    border-radius: 14px;
    border: 1.5px solid var(--input-border);
    font: inherit;
    padding: 10px 12px;
    resize: vertical;
    outline: none;
    background: #fff;
    transition: border-color 0.18s, box-shadow 0.18s, transform 0.08s;
}

.post-composer textarea:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(255, 111, 181, 0.25);
    transform: translateY(-1px);
}

.post-composer-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
    font-size: 12px;
    color: var(--text-soft);
}

/* Dropdowns (Mentions / Shares / Likes) */
.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-toggle {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--text-soft);
    text-decoration: none;
    padding: 6px 10px;
    border-radius: 999px;
    background: rgba(255,255,255,0.85);
    border: 1px solid rgba(255,255,255,0.9);
    cursor: pointer;
    transition: background 0.12s, transform 0.08s, box-shadow 0.12s;
}

.dropdown-toggle:hover {
    background: var(--accent-soft);
    box-shadow: 0 8px 18px rgba(255, 111, 181, 0.35);
    transform: translateY(-1px);
    text-decoration: none;
}

.dd-menu {
    display: none;
    position: absolute;
    right: 0;
    top: 34px;
    background: var(--bg-card);
    border-radius: 14px;
    width: 320px;
    max-height: 300px;
    overflow-y: auto;
    box-shadow: 0 18px 38px rgba(57, 19, 68, 0.45);
    z-index: 999;
}

.dd-menu.show {
    display: block;
}

.dd-item {
    padding: 10px 12px;
    border-bottom: 1px solid rgba(244, 219, 255, 0.9);
    font-size: 13px;
}

.dd-item:last-child {
    border-bottom: none;
}

.dd-item small {
    display: block;
    color: var(--text-soft);
    margin-top: 4px;
    font-size: 11px;
}

.dd-item:hover {
    background: rgba(252, 228, 255, 0.7);
    cursor: pointer;
}

/* Trending & search */
.trending-list div {
    margin-bottom: 6px;
    font-size: 13px;
}

.trending-count {
    font-size: 12px;
    color: var(--text-soft);
}

/* Search form */
.search-form {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 4px;
}

.search-form input {
    padding: 8px 10px;
    border-radius: 10px;
    border: 1.5px solid var(--input-border);
    font: inherit;
    font-size: 13px;
    outline: none;
    background: #fff;
    transition: border-color 0.18s, box-shadow 0.18s, transform 0.08s;
}

.search-form input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(255, 111, 181, 0.25);
    transform: translateY(-1px);
}

.search-form button {
    align-self: flex-start;
    border-radius: 999px;
    border: none;
    padding: 7px 14px;
    font: inherit;
    font-size: 13px;
    font-weight: 500;
    background: linear-gradient(135deg, var(--accent), var(--accent-dark));
    color: #fff;
    cursor: pointer;
    box-shadow: 0 8px 18px rgba(255, 111, 181, 0.4);
    transition: transform 0.08s ease, box-shadow 0.12s ease, filter 0.12s ease;
}

/* Feed action buttons: Edit, Delete, Like, Unlike, Repost, comments, etc. */
#feed button,
#feed input[type="button"],
#feed input[type="submit"] {
    border-radius: 999px;
    border: 1px solid var(--accent-soft);
    background: #ffffff;
    padding: 4px 10px;
    font: inherit;
    font-size: 12px;
    cursor: pointer;
    color: var(--text-soft);
    margin-right: 4px;
    margin-top: 4px;
    transition:
        background 0.12s,
        border-color 0.12s,
        transform 0.08s,
        box-shadow 0.12s;
}

#feed button:hover,
#feed input[type="button"]:hover,
#feed input[type="submit"]:hover {
    background: var(--accent-soft);
    border-color: var(--accent);
    color: var(--accent-dark);
    transform: translateY(-1px);
    box-shadow: 0 6px 14px rgba(255, 111, 181, 0.3);
}


.search-form button:hover {
    filter: brightness(1.05);
    transform: translateY(-1px);
}

/* Feed */
#feed {
    margin-top: 12px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* Responsive */
@media (max-width: 960px) {
    .layout-grid {
        grid-template-columns: minmax(0, 1fr);
    }

    .left-column,
    .right-column {
        position: static;
    }
}

@media (max-width: 640px) {
    .topbar {
        flex-direction: column;
        align-items: flex-start;
    }

    .topbar-right {
        width: 100%;
        justify-content: flex-start;
        gap: 8px;
    }

    .btn-outline {
        margin-left: auto;
    }
}
</style>
</head>
<body>
<div class="app-shell">

    <!-- TOP BAR -->
    <div class="card">
        <div class="card-inner topbar">
            <div class="topbar-left">
                <div class="logo-badge">T</div>
                <div class="app-title">
                    <span>TAltter</span>
                    <span class="logged-in-as">
                        Logged in as <strong><?= htmlspecialchars(current_username()) ?></strong>
                    </span>
                </div>
            </div>

            <div class="topbar-right">
                <?php
                // Mentions count
                $stmt = $mysqli->prepare("
                    SELECT COUNT(*) AS cnt
                    FROM posts p
                    WHERE p.content LIKE ?
                    AND p.user_id != ?
                    AND p.created_at > IFNULL(
                        (SELECT last_seen_mentions FROM users WHERE user_id = ?),
                        '1970-01-01'
                    )
                ");
                $stmt->bind_param("sii",$like,$uid,$uid);
                $stmt->execute();
                $m = $stmt->get_result()->fetch_assoc()['cnt'];

                // Shares count
                $stmt = $mysqli->prepare("
                    SELECT COUNT(*) AS cnt
                    FROM reposts r
                    JOIN posts p ON r.post_id = p.post_id
                    WHERE p.user_id = ?
                    AND r.created_at > IFNULL(
                        (SELECT last_seen_shares FROM users WHERE user_id = ?),
                        '1970-01-01'
                    )
                ");
                $stmt->bind_param("ii",$uid,$uid);
                $stmt->execute();
                $s = $stmt->get_result()->fetch_assoc()['cnt'];

                // Likes count
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
                $stmt->bind_param("ii",$uid,$uid);
                $stmt->execute();
                $lk = $stmt->get_result()->fetch_assoc()['cnt'];
                ?>

                <!-- Mentions dropdown -->
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle" onclick="toggleDropdown('mentions', this);return false;">
                        <span>🔔 Mentions</span>
                        <span class="badge"><?= $m ?></span>
                    </a>
                    <div id="mentions" class="dd-menu">
                        <div class="card-inner">
                            <?php
                            $stmt = $mysqli->prepare("
                                SELECT u.username AS username, p.content AS content, p.created_at AS created_at
                                FROM posts p
                                JOIN users u ON p.user_id = u.user_id
                                WHERE p.content LIKE ?
                                AND p.user_id != ?
                                AND p.created_at > IFNULL(
                                    (SELECT last_seen_mentions FROM users WHERE user_id = ?),
                                    '1970-01-01'
                                )
                                ORDER BY p.created_at DESC
                                LIMIT 10
                            ");
                            $stmt->bind_param("sii",$like,$uid,$uid);
                            $stmt->execute();
                            $r = $stmt->get_result();
                            while ($row = $r->fetch_assoc()) {
                                echo "<div class='dd-item'>
                                        <b>@".htmlspecialchars($row['username'])."</b><br>"
                                        .htmlspecialchars($row['content']).
                                        "<small>{$row['created_at']}</small>
                                    </div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Shares dropdown -->
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle" onclick="toggleDropdown('shares', this);return false;">
                        <span>🔁 Shares</span>
                        <span class="badge"><?= $s ?></span>
                    </a>
                    <div id="shares" class="dd-menu">
                        <div class="card-inner">
                            <?php
                            $stmt = $mysqli->prepare("
                            SELECT u.username AS username, p.content AS content, r.created_at AS created_at
                            FROM reposts r
                            JOIN posts p ON r.post_id = p.post_id
                            JOIN users u ON r.user_id = u.user_id
                            WHERE p.user_id = ?
                                AND r.created_at > IFNULL(
                                    (SELECT last_seen_shares FROM users WHERE user_id = ?),
                                    '1970-01-01'
                                )
                                ORDER BY r.created_at DESC
                                LIMIT 10
                            ");
                            $stmt->bind_param("ii",$uid,$uid);
                            $stmt->execute();
                            $r = $stmt->get_result();
                            while ($row = $r->fetch_assoc()) {
                                echo "<div class='dd-item'>
                                        🔁 <b>".htmlspecialchars($row['username'])."</b> shared:<br>"
                                        .htmlspecialchars($row['content']).
                                        "<small>{$row['created_at']}</small>
                                    </div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Likes dropdown -->
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle" onclick="toggleDropdown('likes', this);return false;">
                        <span>❤️ Likes</span>
                        <span class="badge"><?= $lk ?></span>
                    </a>
                    <div id="likes" class="dd-menu">
                        <div class="card-inner">
                            <?php
                            $stmt = $mysqli->prepare("
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
                            $stmt->bind_param("ii",$uid,$uid);
                            $stmt->execute();
                            $r = $stmt->get_result();
                            while ($row = $r->fetch_assoc()) {
                                echo "<div class='dd-item'>
                                        ❤️ <b>".htmlspecialchars($row['username'])."</b> liked:<br>"
                                        .htmlspecialchars($row['content']).
                                        "<small>{$row['created_at']}</small>
                                    </div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <a href="logout.php" class="btn-outline">Logout</a>
            </div>
        </div>
    </div>

    <!-- MAIN LAYOUT -->
    <div class="layout-grid">

        <!-- LEFT COLUMN -->
        <aside class="left-column">
            <div class="card">
                <div class="card-inner">
                    <h3 class="sidebar-heading">Profile</h3>
                    <p class="profile-handle">@<?= htmlspecialchars(current_username()) ?></p>
                    <div class="sidebar-links">
                        <a href="profile.php?u=<?= urlencode(current_username()) ?>">View profile</a>
                        <a href="index.php">Home</a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-inner">
                    <h4 class="sidebar-heading">Liked hashtags</h4>
                    <p class="sidebar-sub">Click to filter your feed.</p>
                    <div>
                        <?php
                        $stmt = $mysqli->prepare("SELECT h.tag_name FROM followed_hashtags fh JOIN hashtags h ON fh.hashtag_id=h.hashtag_id WHERE fh.user_id = ? LIMIT 10");
                        $stmt->bind_param('i',$uid); 
                        $stmt->execute(); 
                        $r=$stmt->get_result();
                        while($row=$r->fetch_assoc()){
                            echo '<a class="tag-chip" href="index.php?hashtag='.urlencode($row['tag_name']).'">#'.htmlspecialchars($row['tag_name']).'</a>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-inner">
                    <h4 class="sidebar-heading">Liked users</h4>
                    <p class="sidebar-sub">People whose posts you see more.</p>
                    <div>
                    <?php
                    $stmt = $mysqli->prepare("
                        SELECT u.username 
                        FROM follows f 
                        JOIN users u ON f.following_id = u.user_id 
                        WHERE f.follower_id = ? 
                        ORDER BY f.created_at DESC
                        LIMIT 10
                    ");
                    $stmt->bind_param('i',$uid); 
                    $stmt->execute(); 
                    $r=$stmt->get_result();
                    if($r->num_rows > 0) {
                        while($row=$r->fetch_assoc()){
                        echo '<div><a href="profile.php?u='.urlencode($row['username']).'">@'.htmlspecialchars($row['username']).'</a></div>';
                    }
                    } else {
                        echo '<div style="color:#999;font-size:90%;">Not following anyone yet</div>';
                    }
                    ?>
                    </div>
                </div>
            </div>

                        <div class="card">
                <div class="card-inner">
                    <h4 class="sidebar-heading">Search users</h4>
                    <p class="sidebar-sub">Type without the @ symbol.</p>

                    <form method="get" action="index.php" class="search-form">
                        <input
                            name="user"
                            placeholder="e.g. Meli"
                            value="<?= htmlspecialchars($filterUser ?? '') ?>">
                        <button type="submit">Search</button>
                    </form>

                    <?php if (!empty($filterUser)): ?>
                        <div style="margin-top:10px;font-size:13px;">
                            <?php
                            $term = '%'.$filterUser.'%';
                            $stmt = $mysqli->prepare("
                                SELECT username
                                FROM users
                                WHERE username LIKE ?
                                ORDER BY username ASC
                                LIMIT 10
                            ");
                            $stmt->bind_param('s', $term);
                            $stmt->execute();
                            $resUsers = $stmt->get_result();

                            if ($resUsers->num_rows === 0) {
                                echo '<div style="color:#999;">No users found</div>';
                            } else {
                                while ($u = $resUsers->fetch_assoc()) {
                                    echo '<div><a href="profile.php?u='
                                         .urlencode($u['username'])
                                         .'">@'
                                         .htmlspecialchars($u['username'])
                                         .'</a></div>';
                                }
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </aside>

        <!-- MIDDLE COLUMN -->
        <main>
            <?php if ($filterHashtag): ?>
                <div class="card">
                    <div class="card-inner">
                        <h3 class="sidebar-heading">Viewing: #<?= htmlspecialchars($filterHashtag) ?></h3>
                        <?php
                        $stmt = $mysqli->prepare("
                            SELECT COUNT(*) as is_following 
                            FROM followed_hashtags fh
                            JOIN hashtags h ON fh.hashtag_id = h.hashtag_id
                            WHERE fh.user_id = ? AND h.tag_name = ?
                        ");
                        $stmt->bind_param("is", $uid, $filterHashtag);
                        $stmt->execute();
                        $isFollowingHashtag = $stmt->get_result()->fetch_assoc()['is_following'] > 0;
                        ?>

                        <?php if ($isFollowingHashtag): ?>
                            <form method="post" action="follow_hashtag.php" style="margin-top:8px;">
                                <input type="hidden" name="hashtag" value="<?= htmlspecialchars($filterHashtag) ?>">
                                <input type="hidden" name="action" value="unfollow">
                                <button type="submit" class="btn-outline">Unlike #<?= htmlspecialchars($filterHashtag) ?></button>
                            </form>
                        <?php else: ?>
                            <form method="post" action="follow_hashtag.php" style="margin-top:8px;">
                                <input type="hidden" name="hashtag" value="<?= htmlspecialchars($filterHashtag) ?>">
                                <input type="hidden" name="action" value="follow">
                                <button type="submit" class="btn-pill">Like #<?= htmlspecialchars($filterHashtag) ?></button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-inner post-composer">
                    <form method="post" action="post.php">
                        <textarea
                            name="content"
                            maxlength="144"
                            placeholder="What's happening in 144 characters?"></textarea>
                        <div class="post-composer-footer">
                            <span>Use <strong>#hashtags</strong> and <strong>@mentions</strong> to join the tiny chatter.</span>
                            <button class="btn-pill" type="submit">Post</button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="feed">
                <?php include 'fetch_posts.php'; ?>
            </div>
        </main>

        <!-- RIGHT COLUMN -->
        <aside class="right-column">
            <div class="card">
                <div class="card-inner">
                    <h4 class="sidebar-heading">Trending hashtags</h4>
                    <p class="sidebar-sub">Top tags across TAltter.</p>
                    <div class="trending-list">
                        <?php
                        $q = "SELECT h.tag_name, COUNT(*) AS cnt
                              FROM post_hashtags ph
                              INNER JOIN hashtags h ON ph.hashtag_id = h.hashtag_id
                              WHERE h.tag_name <> ''
                              GROUP BY h.tag_name
                              HAVING cnt > 0
                              ORDER BY cnt DESC
                              LIMIT 10";

                        $r = $mysqli->query($q);
                        while ($row = $r->fetch_assoc()) {
                            echo '<div>
                                    <a class="tag-chip" href="index.php?hashtag='.urlencode($row['tag_name']).'">#'.htmlspecialchars($row['tag_name']).'</a>
                                    <span class="trending-count">· '.$row['cnt'].' posts</span>
                                </div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-inner">
                    <h4 class="sidebar-heading">Search hashtag</h4>
                    <p class="sidebar-sub">Type without the # symbol.</p>
                    <form method="get" action="index.php" class="search-form">
                        <input
                            name="hashtag"
                            placeholder="e.g. school"
                            value="<?= htmlspecialchars($filterHashtag) ?>">
                        <button type="submit">Filter feed</button>
                    </form>
                </div>
            </div>
        </aside>

    </div>
</div>

<script>

let CURRENT_USER_ID = <?= json_encode($uid) ?>;

function toggleDropdown(id, el) {
  document.querySelectorAll('.dd-menu').forEach(menu => {
    if (menu.id !== id) menu.classList.remove('show');
  });

  let box = document.getElementById(id);
  let open = box.classList.toggle('show');

  if (open) {
    if (id === 'likes') {
        markSeen('likes');
        let badge = el.querySelector('.badge');
        if (badge) badge.textContent = '0';
        refreshLikesDropdown();
    } else {
        markSeen(id);
        let badge = el.querySelector('.badge');
        if (badge) badge.textContent = '0';
    }
  }
}

function markSeen(type) {
  fetch("mark_seen.php?type=" + type);
}

//  DELETE POST 
function deletePost(postId) {
  if (!confirm("Haluatko varmasti poistaa tämän postauksen?")) return;

  fetch("delete_post.php", {
    method: "POST",
    headers: {"Content-Type": "application/x-www-form-urlencoded"},
    body: "post_id=" + encodeURIComponent(postId)
  })
  .then(res => res.text())
  .then(r => {
    if (r.trim() === "OK") {
      let el = document.getElementById("post-" + postId);
      if (el) el.remove();
    } else {
      alert("Poisto epäonnistui: " + r);
    }
  })
  .catch(err => alert("Virhe: " + err));
}

// EDIT POST
function editPost(postId, currentContent) {
  let contentDiv = document.querySelector('.post-content-' + postId);
  let form = document.createElement('form');
  form.onsubmit = function(e) {
    e.preventDefault();
    savePost(postId);
  };

  let textarea = document.createElement('textarea');
  textarea.id = 'edit-' + postId;
  textarea.value = currentContent;
  textarea.style.width = '100%';
  textarea.style.minHeight = '60px';
  textarea.maxLength = 144;

  let btnSave = document.createElement('button');
  btnSave.textContent = 'Tallenna';
  btnSave.type = 'submit';

  let btnCancel = document.createElement('button');
  btnCancel.textContent = 'Peruuta';
  btnCancel.type = 'button';
  btnCancel.onclick = () => location.reload();

  form.appendChild(textarea);
  form.appendChild(document.createElement('br'));
  form.appendChild(btnSave);
  form.appendChild(document.createTextNode(' '));
  form.appendChild(btnCancel);

  contentDiv.innerHTML = '';
  contentDiv.appendChild(form);
  textarea.focus();
}

// SAVE POST
function savePost(postId) {
  let textarea = document.getElementById('edit-' + postId);
  let content = textarea.value;

  let formData = new FormData();
  formData.append('post_id', postId);
  formData.append('content', content);

  fetch('edit_post.php', { method: 'POST', body: formData })
  .then(r => r.ok ? location.reload() : alert('Virhe tallennuksessa'))
  .catch(err => alert('Virhe: ' + err));
}

//  LIKE
function toggleLike(postId, button, uniqueCardId) {
    const id = (typeof uniqueCardId !== 'undefined' && uniqueCardId !== null) ? uniqueCardId : postId;

    let formData = new FormData();
    formData.append('post_id', postId);

    fetch('like.php', {method: 'POST', body: formData})
    .then(res => res.json())
    .then(data => {
        if (!data.success) return alert(data.error || 'Virhe');

        let icon = document.getElementById('like-icon-' + id);
        let text = document.getElementById('like-text-' + id);
        let count = document.getElementById('like-count-' + id);

        if (icon) icon.textContent = (data.action === 'liked') ? '❤️' : '🤍';
        if (text) text.textContent = (data.action === 'liked') ? 'Unlike' : 'Like';
        if (count) count.textContent = data.count;

        try {
            if (button && button instanceof HTMLElement) {
                button.dataset.liked = (data.action === 'liked') ? '1' : '0';
            }
        } catch (e) { /* ignore */ }

        updateLikesCount();
    })
    .catch(err => {
        console.error('toggleLike error', err);
        alert('Virhe verkossa');
    });
}

function updateLikesCount() {
    fetch('fetch_like.php?json=1')
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;
            
            let likesToggle = document.querySelector('[onclick*="likes"]');
            if (likesToggle) {
                let badge = likesToggle.querySelector('.badge');
                if (badge) {
                    badge.textContent = data.count;
                }
            }
        })
        .catch(err => console.error('updateLikesCount error', err));
}

function refreshLikesDropdown() {
    let menu = document.getElementById('likes');
    if (!menu) return;
    
    fetch('fetch_like.php?json=1')
        .then(res => res.json())
        .then(data => {
            if (!data || !data.success) return;
            let inner = menu.querySelector('.card-inner');
            if (inner) inner.innerHTML = data.html;
        })
        .catch(err => console.error('refreshLikesDropdown', err));
}

(function startLikesPolling(){
    const INTERVAL_MS = 5000;
    setInterval(() => {
        try {
            if (document.hidden) return; 
            updateLikesCount();
        } catch (e) {
            console.error('likes polling', e);
        }
    }, INTERVAL_MS);
})();

//  RELOAD FEED
function refreshFeed() {
  fetch('fetch_posts.php?' + new URLSearchParams(window.location.search))
    .then(res => res.text())
    .then(html => document.getElementById('feed').innerHTML = html);
}

//  DROPBOX CLICK OUTSIDE
document.addEventListener("click", function(e) {
  if (!e.target.closest('.dropdown')) {
    document.querySelectorAll('.dd-menu').forEach(el => el.classList.remove('show'));
  }
});
// COMMENTS
function openComments(postId) {
    let panel = document.getElementById('comment-panel-' + postId);
    if (!panel) return;
    if (panel.style.display === 'none' || panel.style.display === '') {
        panel.style.display = 'block';
        loadComments(postId);
    } else {
        panel.style.display = 'none';
    }
}

function loadComments(postId) {
    let listEl = document.getElementById('comment-list-' + postId);
    if (!listEl) return;
    listEl.innerHTML = 'Loading...';

    fetch('get_comments.php?post_id=' + encodeURIComponent(postId))
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                listEl.innerHTML = '<div style="color:#900">Could not load comments</div>';
                return;
            }
            if (!data.comments || data.comments.length === 0) {
                listEl.innerHTML = '<div style="color:#666;font-size:90%">No comments yet</div>';
            } else {
                listEl.innerHTML = data.comments.map(c => {
                    let controls = '';
                    if (typeof CURRENT_USER_ID !== 'undefined' && c.user_id === CURRENT_USER_ID) {
                        controls = '<div style="margin-top:6px;">'
                            + '<button type="button" onclick="editComment(' + postId + ',' + c.comment_id + ',\'' + encodeURIComponent(c.content) + '\')">Muokkaa</button>'
                            + ' <button type="button" onclick="deleteComment(' + c.comment_id + ',' + postId + ')" style="color:red">Poista</button>'
                            + '</div>';
                    }

                    var editedLabel = '';
                    if (c.edited_at && c.edited_at !== null && c.edited_at !== c.created_at) {
                        editedLabel = ' <small style="color:#999;margin-left:6px;">(muokattu)</small>';
                    }

                    return '<div style="padding:6px 0;border-bottom:1px solid rgba(0,0,0,0.05)" id="comment-' + c.comment_id + '">'
                        + '<a href="profile.php?u=' + encodeURIComponent(c.username) + '"><strong>@' + escapeHtml(c.username) + '</strong></a> '
                        + '<div class="comment-body-' + c.comment_id + '" style="font-size:14px;color:#111;margin-top:4px;">' + escapeHtml(c.content) + '</div>'
                        + '<div><small style="color:#999">' + c.created_at + '</small>' + editedLabel + '</div>'
                        + controls
                        + '</div>';
                }).join('');
            }

            let cntEl = document.getElementById('comment-count-' + postId);
            if (cntEl) cntEl.textContent = data.comments ? data.comments.length : 0;
        })
        .catch(err => {
            listEl.innerHTML = '<div style="color:#900">Error loading comments</div>';
            console.error('loadComments error', err);
        });
}

function postComment(postId) {
    let input = document.getElementById('comment-input-' + postId);
    if (!input) return;

    let content = input.value.trim();
    if (!content) return;

    let formData = new FormData();
    formData.append('post_id', postId);
    formData.append('content', content);

    fetch('comment.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert(data.error || 'Could not post comment');
                return;
            }

            input.value = '';
            let cntEl = document.getElementById('comment-count-' + postId);
            if (cntEl) cntEl.textContent = data.count;

            let panel = document.getElementById('comment-panel-' + postId);
            if (panel && panel.style.display === 'block') {
                loadComments(postId);
            }
        })
        .catch(err => {
            console.error('postComment error', err);
            alert('Error sending comment');
        });
}

function escapeHtml(s) {
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function editComment(postId, commentId, encodedContent) {
    let content = decodeURIComponent(encodedContent || '');
    let bodyEl = document.querySelector('.comment-body-' + commentId);
    if (!bodyEl) return;

    if (bodyEl.querySelector('textarea')) return;

    let textarea = document.createElement('textarea');
    textarea.style.width = '100%';
    textarea.style.minHeight = '60px';
    textarea.maxLength = 144;
    textarea.value = content;

    let btnSave = document.createElement('button');
    btnSave.textContent = 'Tallenna';
    btnSave.type = 'button';
    btnSave.onclick = function() { saveEditedComment(commentId, postId); };

    let btnCancel = document.createElement('button');
    btnCancel.textContent = 'Peruuta';
    btnCancel.type = 'button';
    btnCancel.onclick = function() { cancelEdit(commentId); };

    bodyEl.innerHTML = '';
    bodyEl.appendChild(textarea);
    bodyEl.appendChild(document.createElement('br'));
    bodyEl.appendChild(btnSave);
    bodyEl.appendChild(document.createTextNode(' '));
    bodyEl.appendChild(btnCancel);
    textarea.focus();
}

function cancelEdit(commentId) {
    let commentEl = document.getElementById('comment-' + commentId);
    if (!commentEl) return;
    let postPanel = commentEl.closest('[id^="comment-panel-"]');
    if (!postPanel) return;
    let postId = postPanel.id.replace('comment-panel-', '');
    loadComments(postId);
}

function saveEditedComment(commentId, postId) {
    let textarea = document.querySelector('#comment-' + commentId + ' textarea');
    if (!textarea) return;
    let content = textarea.value.trim();
    if (!content) return alert('Kommentti ei voi olla tyhjä');

    let fd = new FormData();
    fd.append('comment_id', commentId);
    fd.append('content', content);

    fetch('edit_comment.php', { method: 'POST', body: fd })
        .then(res => res.text())
        .then(text => {
            let data = null;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('saveEditedComment: response not JSON', text);
                alert('Server error while editing comment. See console for details.');
                return;
            }
            if (!data.success) return alert(data.error || 'Edit failed');
            loadComments(postId);
        })
        .catch(err => {
            console.error('saveEditedComment', err);
            alert('Error saving comment');
        });
}

function deleteComment(commentId, postId) {
    if (!confirm('Haluatko varmasti poistaa tämän kommentin?')) return;
    let fd = new FormData();
    fd.append('comment_id', commentId);

    fetch('delete_comment.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            if (!data.success) return alert(data.error || 'Delete failed');
            loadComments(postId);
            let cntEl = document.getElementById('comment-count-' + postId);
            if (cntEl && typeof data.count !== 'undefined') cntEl.textContent = data.count;
        })
        .catch(err => {
            console.error('deleteComment', err);
            alert('Error deleting comment');
        });
}

(function startCommentPolling(){
    const INTERVAL_MS = 10000;

    setInterval(() => {
        try {
            if (document.hidden) return;
            refreshCommentCounts();
        } catch (e) {
            console.error('comment polling error', e);
        }
    }, INTERVAL_MS);
})();

function refreshCommentCounts() {
    let ids = [];
    document.querySelectorAll('[id^="comment-count-"]').forEach(el => {
        let id = el.id.replace('comment-count-', '');
        if (id) ids.push(id);
    });

    if (ids.length === 0) return;

    fetch('fetch_comment_counts.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ post_ids: ids })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) return;

        let counts = data.counts;

        for (let postId in counts) {
            let el = document.getElementById('comment-count-' + postId);
            if (el) el.textContent = counts[postId];
        }
    })
    .catch(err => console.error('refreshCommentCounts error', err));
}

console.log("JS LOADED OK");
</script>
</body>
</html>