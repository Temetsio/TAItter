<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];

/* Fetch current user */
$stmt = $mysqli->prepare("SELECT username, bio, profile_picture_url FROM users WHERE user_id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$errors = [];
$usernameCurrent = $user['username'] ?? '';
$bioCurrent = $user['bio'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* BIO */
    $bio = trim($_POST['bio'] ?? '');
    $bioCurrent = $bio;
    if (strlen($bio) > 160) {
        $errors[] = "Bio can be max 160 characters.";
    }

    /* USERNAME */
    $newUsername = trim($_POST['username'] ?? '');
    $finalUsername = $usernameCurrent;

    if ($newUsername !== '' && $newUsername !== $usernameCurrent) {
        if (strlen($newUsername) < 3 || strlen($newUsername) > 20) {
            $errors[] = "Username must be 3–20 characters long.";
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $newUsername)) {
            $errors[] = "Username may only contain letters, numbers, and underscores.";
        }

        $stmt = $mysqli->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
        $stmt->bind_param("si", $newUsername, $uid);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Username is already taken.";
        }

        if (empty($errors)) $finalUsername = $newUsername;
    }

    /* PROFILE IMAGE */
    $imgPath = $user['profile_picture_url'];

    if (!empty($_FILES['profile_picture']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir);
        $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];

        if (!in_array($ext, $allowed)) {
            $errors[] = "Only JPG, PNG or WEBP allowed.";
        } else {
            $newName = "pfp_" . $uid . "_" . time() . "." . $ext;
            $imgPath = $targetDir . $newName;
            move_uploaded_file($_FILES['profile_picture']['tmp_name'], $imgPath);
        }
    }

    /* SAVE */
    if (empty($errors)) {
        $stmt = $mysqli->prepare("UPDATE users SET username = ?, bio = ?, profile_picture_url = ? WHERE user_id = ?");
        $stmt->bind_param("sssi", $finalUsername, $bio, $imgPath, $uid);
        $stmt->execute();

        header("Location: profile.php?u=".urlencode($finalUsername));
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
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
:root {
    --accent: #ff6fb5;
    --accent-dark: #ff4fa5;
    --accent-soft: #ffe5f3;
    --bg-card: rgba(255, 255, 255, 0.97);
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
    padding: 32px 16px;
    color: var(--text-main);
    display: block;
}


.app-shell{
    width: 100%;
    max-width: 900px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 16px;
}



.card{
    max-width: none;
    margin: 0;
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(18px);
    border-radius: 18px;
    padding: 16px 18px;
    box-shadow: 0 16px 35px rgba(255,105,180,0.25);
    position: relative;
    overflow: hidden;
}

.card::before{
    content: "";
    position: absolute;
    inset: -40px;
    background:
        radial-gradient(circle at top left, rgba(255,255,255,0.6), transparent 55%),
        radial-gradient(circle at bottom right, rgba(255,255,255,0.5), transparent 55%);
    opacity: .5;
    pointer-events: none;
}

.card-inner{
    position: relative;
    z-index: 1;
}


.topbar{
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.topbar-left{
    display: flex;
    align-items: center;
    gap: 10px;
}

.logo-badge{
    width: 40px;
    height: 40px;
    border-radius: 14px;
    background: linear-gradient(135deg,#ff6fb5,#ffc75f);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.app-title{
    display: flex;
    flex-direction: column;
}

.app-title span:first-child{
    font-weight: 600;
    font-size: 18px;
}

.app-title span:last-child{
    font-size: 12px;
    color: #5c4969;
}

.topbar-right{
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.btn-outline{
    border-radius: 999px;
    border: 1.5px solid #ffe5f3;
    background: rgba(255,255,255,0.9);
    padding: 7px 14px;
    font: inherit;
    font-size: 13px;
    cursor: pointer;
    color: #5c4969;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    transition: background 0.12s, border-color 0.12s, transform 0.08s;
}

.btn-outline:hover{
    background: #ffe5f3;
    border-color: #ff6fb5;
    transform: translateY(-1px);
}



.card {
    width: 100%;
    max-width: 900px;
    background: var(--bg-card);
    backdrop-filter: blur(18px);
    border-radius: 24px;
    padding: 32px 40px 28px;
    box-shadow: 0 16px 35px rgba(255, 105, 180, 0.25);
}

h2 {
    margin: 0 0 24px;
    font-size: 26px;
}

form > label {
    display: block;
    margin-top: 18px;
    margin-bottom: 6px;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-soft);
}

input,
textarea {
    width: 100%;
    padding: 11px 12px;
    border-radius: 14px;
    border: 1.5px solid var(--input-border);
    font: inherit;
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s, transform 0.08s;
}

input:focus,
textarea:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(255, 111, 181, 0.25);
    transform: translateY(-1px);
}

textarea {
    min-height: 110px;
    resize: vertical;
}

.counter {
    font-size: 11px;
    color: var(--text-soft);
    text-align: right;
    margin-top: 4px;
}

.error {
    background: #ffe5f1;
    color: #b0003a;
    padding: 10px 12px;
    border-radius: 14px;
    margin-bottom: 8px;
    font-size: 13px;
}

.preview {
    display: flex;
    gap: 16px;
    align-items: center;
    margin-top: 8px;
}

.preview img {
    width: 96px;
    height: 96px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 12px 26px rgba(255, 111, 181, 0.45);
}

.file-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 16px;
    border-radius: 999px;
    background: linear-gradient(135deg, var(--accent), var(--accent-dark));
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 10px 22px rgba(255, 111, 181, 0.45);
    border: none;
    transition: filter 0.12s, transform 0.08s;
}

.file-btn:hover {
    filter: brightness(1.05);
    transform: translateY(-1px);
}

#fileName {
    font-size: 13px;
    color: var(--text-soft);
}

.save-btn {
    margin-top: 24px;
    border: none;
    padding: 10px 24px;
    border-radius: 999px;
    background: linear-gradient(135deg, var(--accent), var(--accent-dark));
    color: #fff;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    box-shadow: 0 12px 26px rgba(255, 111, 181, 0.45);
    transition: filter 0.12s, transform 0.08s;
}

.save-btn:hover {
    filter: brightness(1.05);
    transform: translateY(-1px);
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
                <a href="index.php" class="btn-outline">← Back to feed</a>
                <a href="logout.php" class="btn-outline">Logout</a>
            </div>
        </div>
    </div>

    <!-- Edit form card -->
    <div class="card">
        <div class="card-inner">
            <h2>Edit profile</h2>

            <?php foreach ($errors as $e): ?>
                <div class="error"><?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>

            <form method="post" enctype="multipart/form-data">

<label>Username</label>
<input type="text" name="username" value="<?= htmlspecialchars($usernameCurrent) ?>">

<label>Bio</label>
<textarea name="bio" id="bio" maxlength="160"><?= htmlspecialchars($bioCurrent) ?></textarea>
<div class="counter" id="counter"></div>

<label>Profile picture</label>
<div class="preview">
<?php if ($user['profile_picture_url']): ?>
<img src="<?= htmlspecialchars($user['profile_picture_url']) ?>">
<?php endif; ?>

<input id="profile_picture" type="file" name="profile_picture" accept=".jpg,.jpeg,.png,.webp" hidden>
<label for="profile_picture" class="file-btn">Choose file</label>
<span id="fileName"></span>
</div>

<button class="save-btn">Save changes</button>


</form>
</div>

<script>
const bio=document.getElementById("bio");
const counter=document.getElementById("counter");
counter.innerText=bio.value.length+" / 160";
bio.addEventListener("input",()=>counter.innerText=bio.value.length+" / 160");

const file=document.getElementById("profile_picture");
const fileName=document.getElementById("fileName");
file.addEventListener("change",()=>fileName.innerText=file.files[0]?.name || "");
</script>

</body>
</html>
