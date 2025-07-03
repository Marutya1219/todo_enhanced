<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

// 編集か新規か
$is_edit = isset($_GET['id']);
$error = '';
$title = $detail = $due_date = $priority = $status = '';
if ($is_edit) {
    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ?');
    $stmt->execute([$_GET['id'], $user_id]);
    $task = $stmt->fetch();
    if (!$task) {
        header('Location: todo.php');
        exit();
    }
    $title = $task['title'];
    $detail = $task['detail'];
    $due_date = $task['due_date'];
    $priority = $task['priority'];
    $status = $task['status'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $detail = trim($_POST['detail'] ?? '');
    $due_date = $_POST['due_date'] ?? null;
    $priority = $_POST['priority'] ?? '中';
    $status = $_POST['status'] ?? '未完了';
    if ($title === '') {
        $error = 'タスク名を入力してください。';
    } else {
        if ($is_edit) {
            $stmt = $pdo->prepare('UPDATE tasks SET title=?, detail=?, due_date=?, priority=?, status=? WHERE id=? AND user_id=?');
            $stmt->execute([$title, $detail, $due_date, $priority, $status, $_GET['id'], $user_id]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO tasks (user_id, title, detail, due_date, priority, status) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$user_id, $title, $detail, $due_date, $priority, $status]);
        }
        header('Location: todo.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>タスク<?= $is_edit ? '編集' : '追加' ?> | ToDoリスト</title>
    <link rel="stylesheet" href="./css/task_form.css">
</head>
<body>
    <div class="task-form-container">
        <h2>タスク<?= $is_edit ? '編集' : '追加' ?></h2>
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <label for="title">タスク名</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($title) ?>" required>

            <label for="detail">詳細</label>
            <textarea id="detail" name="detail" rows="3"><?= htmlspecialchars($detail) ?></textarea>

            <label for="due_date">期限</label>
            <input type="date" id="due_date" name="due_date" value="<?= htmlspecialchars($due_date) ?>">

            <label for="priority">優先度</label>
            <select id="priority" name="priority">
                <option value="低" <?= $priority === '低' ? 'selected' : '' ?>>低</option>
                <option value="中" <?= $priority === '中' ? 'selected' : '' ?>>中</option>
                <option value="高" <?= $priority === '高' ? 'selected' : '' ?>>高</option>
            </select>

            <label for="status">状態</label>
            <select id="status" name="status">
                <option value="未完了" <?= $status === '未完了' ? 'selected' : '' ?>>未完了</option>
                <option value="完了" <?= $status === '完了' ? 'selected' : '' ?>>完了</option>
            </select>

            <button class="btn" type="submit">保存</button>
            <a href="todo.php" class="cancel-btn">キャンセル</a>
        </form>
    </div>
</body>
</html> 