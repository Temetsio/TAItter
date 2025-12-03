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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    
    $bio = trim($_POST['bio']);
    if (strlen($bio) > 160) {
        $errors[] = "Bio saa olla max 160 merkkiä.";
    }


    $imgPath = $user['profile_picture_url'];

    if (!empty($_FILES['profile_picture']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir);

        $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg','jpeg','png','webp'];

        if (!in_array(strtolower($ext), $allowed)) {
            $errors[] = "Vain JPG, PNG tai WEBP on sallittu.";
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
        header("Location: profile.php?u=".$user['username']);
        exit;
    }
}
?>
<!doctype html>
<html><head>
<meta charset="utf-8">
<title>Edit profile</title>
</head><body>
<h2>Edit profile</h2>

<?php foreach ($errors as $e) echo "<p style='color:red'>$e</p>"; ?>

<form method="post" enctype="multipart/form-data">

<textarea name="bio" maxlength="160" rows="5" style="width:100%;"><?=htmlspecialchars($user['bio'])?></textarea>
<br>
<small>Max 160 merkkiä</small>
<br><br>

<?php if ($user['profile_picture_url']): ?>
<img src="<?=htmlspecialchars($user['profile_picture_url'])?>" width="120"><br>
<?php endif; ?>

<input type="file" name="profile_picture">
<br><br>

<button type="submit">Save</button>
</form>

<p><a href="profile.php?u=<?=$user['username']?>">← Back</a></p>
</body></html>
