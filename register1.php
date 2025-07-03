<?php
session_start();
require_once 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($username === '' || $password === '' || $password_confirm === '') {
        $error = '全ての項目を入力してください。';
    } elseif ($password !== $password_confirm) {
        $error = 'パスワードが一致しません。';
    } else {
        // ユーザー名重複チェック
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'このユーザー名は既に使われています。';
        } else {
            // 登録処理
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
            $stmt->execute([$username, $hash]);
            header('Location: login1.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ユーザー登録 | ToDoリスト</title>
    <link rel="stylesheet" href="./css/register.css">
</head>
<body>
    <div class="register-container">
        <h2>ユーザー登録</h2>
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form action="" method="post">
            <label for="username">ユーザー名</label>
            <input type="text" id="username" name="username" required>

            <label for="password">パスワード</label>
            <input type="password" id="password" name="password" required>

            <label for="password_confirm">パスワード（確認）</label>
            <input type="password" id="password_confirm" name="password_confirm" required>

            <button class="btn" type="submit">登録</button>
        </form>
        <a class="login-link" href="login1.php">ログインはこちら</a>
    </div>
</body>
</html> 