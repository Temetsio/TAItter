<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];

$stmt = $mysqli->prepare("SELECT username,bio,profile_picture_url FROM users WHERE user_id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

$errors = [];
$bioCurrent = $user['bio'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $bio = trim($_POST['bio']);
    $bioCurrent = $bio; 

    if (strlen($bio) > 160) {
        $errors[] = "Bio can be max 160 characters.";
    }

    $imgPath = $user['profile_picture_url'];

    if (!empty($_FILES['profile_picture']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir);

        $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg','jpeg','png','webp'];

        if (!in_array(strtolower($ext), $allowed)) {
            $errors[] = "Only JPG, PNG or WEBP allowed.";
        } else {
            $newName = "pfp_".$uid."_".time().".".$ext;
            $targetFile = $targetDir . $newName;

            move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile);
            $imgPath = $targetFile;
        }
    }

    if (empty($errors)) {
        $stmt = $mysqli->prepare("UPDATE users SET bio = ?, profile_picture_url = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $bio, $imgPath, $uid);
        $stmt->execute();
        header("Location: profile.php?u=".urlencode($user['username']));
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Edit profile – TAltter</title>
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
    max-width: 720px;
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

/* Topbar */
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

/* Buttons & links */
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
    margin-top: 8px;
    border-radius: 999px;
    border: none;
    padding: 10px 16px;
    font: inherit;
    font-size: 14px;
    font-weight: 600;
    background: linear-gradient(135deg, var(--accent), var(--accent-dark));
    color: #fff;
    cursor: pointer;
    box-shadow: 0 10px 22px rgba(255, 111, 181, 0.45);
    transition: transform 0.08s ease, box-shadow 0.12s ease, filter 0.12s ease;
}

.btn-primary:hover {
    filter: brightness(1.05);
    transform: translateY(-1px);
    box-shadow: 0 12px 26px rgba(255, 111, 181, 0.5);
}

/* Heading */
.page-title {
    margin: 0 0 4px;
    font-size: 22px;
    font-weight: 600;
}

.page-subtitle {
    margin: 0 0 14px;
    font-size: 13px;
    color: var(--text-soft);
}

/* Errors */
.errors {
    margin-bottom: 12px;
}

.error-message {
    background: #ffe2ea;
    color: #b0003a;
    border-radius: 12px;
    padding: 9px 11px;
    font-size: 13px;
    margin-bottom: 6px;
    border: 1px solid #ffb5cd;
}

/* Form */
.form-grid {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.field-label {
    font-size: 13px;
    font-weight: 500;
    color: var(--text-main);
    margin-bottom: 4px;
}

textarea {
    width: 100%;
    border-radius: 12px;
    border: 1.5px solid var(--input-border);
    font: inherit;
    padding: 10px 12px;
    resize: vertical;
    outline: none;
    min-height: 90px;
    background: #fff;
    transition: border-color 0.18s, box-shadow 0.18s, transform 0.08s;
}

textarea:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(255, 111, 181, 0.25);
    transform: translateY(-1px);
}

.helper-text {
    font-size: 12px;
    color: var(--text-soft);
    margin-top: 4px;
}

.profile-preview {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-top: 4px;
}

.profile-preview img {
    width: 96px;
    height: 96px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 10px 22px rgba(0,0,0,0.18);
}

.file-input {
    font-size: 13px;
    margin-top: 4px;
}

.char-counter {
    font-size: 11px;
    color: var(--text-soft);
    text-align: right;
}

/* Responsive */
@media (max-width: 640px) {
    .topbar {
        flex-direction: column;
        align-items: flex-start;
    }

    .topbar-right {
        width: 100%;
        justify-content: flex-start;
    }

    .profile-preview {
        flex-direction: column;
        align-items: flex-start;
    }



}
</style>
</head>
<body>
<div class="app-shell">

    <!-- Top bar -->
    <div class="card">
        <div class="card-inner topbar">
            <div class="topbar-left">
                <div class="logo-badge">T</div>
                <div class="app-title">
                    <span>TAltter</span>
                    <span>Edit profile</span>
                </div>
            </div>
            <div class="topbar-right">
                <a href="profile.php?u=<?= urlencode($user['username']) ?>" class="btn-outline">← Back to profile</a>
            </div>
        </div>
    </div>

    <!-- Edit form -->
    <div class="card">
        <div class="card-inner">
            <h1 class="page-title">Edit your profile</h1>
            <p class="page-subtitle">Update your bio and profile picture.</p>

            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <?php foreach ($errors as $e): ?>
                        <div class="error-message">
                            <?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" class="form-grid">
                <div>
                    <label class="field-label" for="bio">Bio</label>
                    <textarea
                        id="bio"
                        name="bio"
                        maxlength="160"
                        rows="5"
                    ><?= htmlspecialchars($bioCurrent) ?></textarea>
                    <div class="helper-text">
                        Max 160 characters.
                    </div>
                    <div id="bio-counter" class="char-counter"></div>
                </div>

                <div>
                    <span class="field-label">Profile picture</span>
                    <div class="profile-preview">
                        <?php if (!empty($user['profile_picture_url'])): ?>
                            <img src="<?= htmlspecialchars($user['profile_picture_url']) ?>" alt="Profile picture">
                        <?php else: ?>
                            <div style="width:96px;height:96px;border-radius:50%;background:linear-gradient(135deg,#ff6fb5,#ffc75f);display:flex;align-items:center;justify-content:center;color:#fff;font-size:36px;font-weight:700;">
                                <?= strtoupper(htmlspecialchars(substr($user['username'], 0, 1))) ?>
                            </div>
                        <?php endif; ?>

                        <div class="profile-preview">
                        <input id="profile_picture" type="file" name="profile_picture" accept=".jpg,.jpeg,.png,.webp" hidden>
                        <label for="profile_picture" class="custom-file-label">Choose File</label>
                        <div class="helper-text">Only JPG, PNG or WEBP is allowed.</div>
</div>
                    </div>
                </div>

                <div>
                    <button type="submit" class="btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const bioTextarea = document.getElementById('bio');
const counter = document.getElementById('bio-counter');

function updateCounter() {
    const max = 160;
    const len = bioTextarea.value.length;
    counter.textContent = len + ' / ' + max;
}

if (bioTextarea && counter) {
    updateCounter();
    bioTextarea.addEventListener('input', updateCounter);
}
</script>
</body>
</html>
