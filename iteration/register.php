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
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>TAltter – Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --accent: #ff6fb5;
            --accent-dark: #ff4fa5;
            --bg-card: rgba(255, 255, 255, 0.9);
            --input-border: #ffd4ec;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Poppins", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 40%, #fbc2eb 100%);
        }

        .auth-wrapper {
            width: 100%;
            max-width: 420px;
        }

        .card {
            background: var(--bg-card);
            backdrop-filter: blur(18px);
            border-radius: 20px;
            padding: 28px 26px 26px;
            box-shadow: 0 20px 40px rgba(255, 105, 180, 0.25);
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
            opacity: 0.6;
            pointer-events: none;
        }

        .card-inner {
            position: relative;
            z-index: 1;
        }

        .logo-badge {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: linear-gradient(135deg, #ff6fb5, #ffc75f);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 22px;
            margin-bottom: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        h1 {
            margin: 0 0 6px;
            font-size: 26px;
            font-weight: 600;
            color: #1f1033;
        }

        .subtitle {
            margin: 0 0 20px;
            font-size: 14px;
            color: #5c4969;
        }

        .subtitle span {
            font-weight: 600;
            color: var(--accent);
        }

        .errors {
            margin-bottom: 16px;
        }

        .error-message {
            background: #ffe2ea;
            color: #b0003a;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 13px;
            margin-bottom: 8px;
            border: 1px solid #ffb5cd;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 6px;
            font-size: 13px;
            color: #392447;
        }

        .field label {
            font-weight: 500;
        }

        .field input {
            padding: 10px 12px;
            border-radius: 10px;
            border: 1.5px solid var(--input-border);
            outline: none;
            font: inherit;
            background: #fff;
            transition: border-color 0.18s, box-shadow 0.18s, transform 0.08s;
        }

        .field input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(255, 111, 181, 0.25);
            transform: translateY(-1px);
        }

        .btn-primary {
            margin-top: 8px;
            border: none;
            border-radius: 999px;
            padding: 11px 14px;
            font: inherit;
            font-weight: 600;
            color: #fff;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            cursor: pointer;
            box-shadow: 0 12px 25px rgba(255, 111, 181, 0.4);
            transition: transform 0.08s ease, box-shadow 0.12s ease, filter 0.12s ease;
        }

        .btn-primary:hover {
            filter: brightness(1.05);
            box-shadow: 0 14px 28px rgba(255, 111, 181, 0.45);
            transform: translateY(-1px);
        }

        .btn-primary:active {
            transform: translateY(1px);
            box-shadow: 0 8px 18px rgba(255, 111, 181, 0.38);
        }

        .bottom-text {
            margin-top: 14px;
            font-size: 13px;
            color: #5c4969;
            text-align: center;
        }

        .bottom-text a {
            color: var(--accent-dark);
            font-weight: 600;
            text-decoration: none;
        }

        .bottom-text a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .card {
                padding: 22px 18px 20px;
            }

            h1 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
<div class="auth-wrapper">
    <div class="card">
        <div class="card-inner">
            <div class="logo-badge">T</div>
            <h1>Create your TAltter</h1>
            <p class="subtitle">
                Join the <span>tiny posts</span> universe – it takes less than a minute.
            </p>

            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <?php foreach ($errors as $e): ?>
                        <div class="error-message">
                            <?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="field">
                    <label for="username">Username</label>
                    <input
                        id="username"
                        name="username"
                        required
                        value="<?php echo isset($username) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : ''; ?>"
                        placeholder="@yourhandle">
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        required
                        value="<?php echo isset($email) ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : ''; ?>"
                        placeholder="you@example.com">
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        placeholder="Choose something secret">
                </div>

                <button class="btn-primary" type="submit">Sign up</button>
            </form>

            <p class="bottom-text">
                Already tweeting tiny thoughts?
                <a href="login.php">Log in instead</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
