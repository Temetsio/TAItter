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
body{
margin:0;font-family:Poppins,sans-serif;
background:linear-gradient(135deg,#ff9a9e,#fad0c4,#fbc2eb);
padding:20px;color:#1f1033
}
.card{
max-width:720px;margin:auto;
background:rgba(255,255,255,.95);
padding:18px;border-radius:18px;
box-shadow:0 16px 35px rgba(0,0,0,.25)
}
label{font-size:13px;font-weight:600}
input,textarea{
width:100%;padding:10px;
border-radius:12px;border:1.5px solid #ffd4ec;
font:inherit
}
textarea{min-height:90px}
button{
margin-top:10px;border:none;
padding:10px 20px;border-radius:999px;
background:linear-gradient(135deg,#ff6fb5,#ff4fa5);
color:#fff;font-weight:600;cursor:pointer
}
.error{
background:#ffe2ea;color:#b0003a;
padding:8px;border-radius:12px;
margin-bottom:6px;font-size:13px
}
.preview{display:flex;gap:16px;align-items:center;margin-top:6px}
.preview img{
width:96px;height:96px;border-radius:50%;
object-fit:cover;box-shadow:0 10px 20px rgba(0,0,0,.2)
}
.file-btn{
display:inline-block;padding:8px 14px;
background:linear-gradient(135deg,#ff6fb5,#ff4fa5);
color:white;border-radius:999px;
cursor:pointer;font-size:13px;font-weight:600
}
.file-btn:hover{filter:brightness(1.05)}
.counter{font-size:11px;color:#5c4969;text-align:right}
</style>
</head>
<body>

<div class="card">
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

<button>Save changes</button>

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
