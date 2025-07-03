<?php
session_start();
require_once 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        $error = 'ユーザー名とパスワードを入力してください。';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: todo.php');
            exit();
        } else {
            $error = 'ユーザー名またはパスワードが違います。';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ログイン | ToDoリスト</title>
    <link rel="stylesheet" href="./css/login.css">
</head>
<body>
    <div class="login-container">
        <h2>ログイン</h2>
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form action="" method="post">
            <label for="username">ユーザー名</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">パスワード</label>
            <input type="password" id="password" name="password" required>
            
            <button class="btn" type="submit">ログイン</button>
        </form>
        <a class="register-link" href="register 1.php">ユーザー登録はこちら</a>
    </div>
</body>
</html>