<?php
require_once 'config.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$email || !$password) $errors[] = "Fill all fields.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email.";

    if (empty($errors)) {
        $stmt = $mysqli->prepare("SELECT user_id FROM users WHERE username=? OR email=?");
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows) {
            $errors[] = "Username or email already taken.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("INSERT INTO users (username,email,password_hash) VALUES (?,?,?)");
            $stmt->bind_param('sss', $username, $email, $hash);
            if ($stmt->execute()) {
                header('Location: login.php');
                exit;
            } else {
                $errors[] = "Database error: " . $stmt->error;
            }
        }
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Register</title></head>
<body>
<h2>Register</h2>
<?php foreach($errors as $e) echo "<div style='color:red;'>$e</div>"; ?>
<form method="post">
    <label>Username<br><input name="username" required></label><br>
    <label>Email<br><input name="email" type="email" required></label><br>
    <label>Password<br><input name="password" type="password" required></label><br>
    <button>Register</button>
</form>
<a href="login.php">Login</a>
</body>
</html>
