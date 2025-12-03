<?php
require_once 'config.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    if (!$u || !$p) $errors[] = "Fill all fields.";

    if (empty($errors)) {
        $stmt = $mysqli->prepare("SELECT user_id,username,password_hash FROM users WHERE username=? OR email=?");
        $stmt->bind_param('ss', $u, $u);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        if ($row) {
            $stored = $row['password_hash'];
            $ok = false;
            if (password_verify($p, $stored)) $ok = true;
            elseif ($stored === $p) $ok = true;
            if ($ok) {
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['username'] = $row['username'];
                header('Location: index.php');
                exit;
            } else $errors[] = "Invalid credentials.";
        } else $errors[] = "Invalid credentials.";
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Login</title></head>
<body>
<h2>Login</h2>
<?php foreach($errors as $e) echo "<div style='color:red;'>$e</div>"; ?>
<form method="post">
    <label>Username or Email<br><input name="username" required></label><br>
    <label>Password<br><input name="password" type="password" required></label><br>
    <button>Login</button>
</form>
<a href="register.php">Register</a>
</body>
</html>
