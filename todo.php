<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// タスク追加処理
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {
    $title = trim($_POST['title'] ?? '');
    $detail = trim($_POST['detail'] ?? '');
    $due_date = $_POST['due_date'] ?? null;
    $priority = $_POST['priority'] ?? '中';
    if ($title === '') {
        $error = 'タスク名を入力してください。';
    } else {
        $stmt = $pdo->prepare('INSERT INTO tasks (user_id, title, detail, due_date, priority) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$user_id, $title, $detail, $due_date, $priority]);
        header('Location: todo.php');
        exit();
    }
}

// 検索・フィルタ
$where = 'user_id = ?';
$params = [$user_id];
if (!empty($_GET['keyword'])) {
    $where .= ' AND title LIKE ?';
    $params[] = '%' . $_GET['keyword'] . '%';
}
if (!empty($_GET['priority'])) {
    $where .= ' AND priority = ?';
    $params[] = $_GET['priority'];
}
if (!empty($_GET['status'])) {
    $where .= ' AND status = ?';
    $params[] = $_GET['status'];
}
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE $where ORDER BY due_date ASC, id DESC");
$stmt->execute($params);
$tasks = $stmt->fetchAll();

// 進捗計算
$stmt2 = $pdo->prepare('SELECT COUNT(*) FROM tasks WHERE user_id = ?');
$stmt2->execute([$user_id]);
$total = $stmt2->fetchColumn();
$stmt3 = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND status = '完了'");
$stmt3->execute([$user_id]);
$done = $stmt3->fetchColumn();
$progress = ($total > 0) ? round($done / $total * 100) : 0;
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ToDoリスト</title>
    <link rel="stylesheet" href="./css/todo.css">
</head>
<body>
    <div class="todo-container">
        <header>
            <div class="user-info">
                <span>こんにちは、<?= htmlspecialchars($username) ?>さん</span>
                <a href="logout.php" class="logout-btn">ログアウト</a>
            </div>
            <h2>ToDoリスト</h2>
        </header>
        <section class="add-task">
            <?php if ($error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post">
                <input type="text" name="title" placeholder="タスク名" required>
                <input type="text" name="detail" placeholder="詳細">
                <input type="date" name="due_date">
                <select name="priority">
                    <option value="低">低</option>
                    <option value="中" selected>中</option>
                    <option value="高">高</option>
                </select>
                <button class="btn" type="submit" name="add_task">追加</button>
            </form>
        </section>
        <section class="filter-task">
            <form method="get">
                <input type="text" name="keyword" placeholder="検索ワード" value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
                <select name="priority">
                    <option value="">優先度(全て)</option>
                    <option value="低" <?= (($_GET['priority'] ?? '') === '低') ? 'selected' : '' ?>>低</option>
                    <option value="中" <?= (($_GET['priority'] ?? '') === '中') ? 'selected' : '' ?>>中</option>
                    <option value="高" <?= (($_GET['priority'] ?? '') === '高') ? 'selected' : '' ?>>高</option>
                </select>
                <select name="status">
                    <option value="">状態(全て)</option>
                    <option value="未完了" <?= (($_GET['status'] ?? '') === '未完了') ? 'selected' : '' ?>>未完了</option>
                    <option value="完了" <?= (($_GET['status'] ?? '') === '完了') ? 'selected' : '' ?>>完了</option>
                </select>
                <button class="btn" type="submit">検索</button>
            </form>
        </section>
        <section class="progress-bar">
            <div class="bar-bg">
                <div class="bar-fill" style="width:<?= $progress ?>%">進捗 <?= $progress ?>%</div>
            </div>
        </section>
        <section class="task-list">
            <table>
                <thead>
                    <tr>
                        <th>状態</th><th>タスク</th><th>詳細</th><th>期限</th><th>優先度</th><th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td class="<?= $task['status'] === '完了' ? 'status-done' : '' ?>"><?= htmlspecialchars($task['status']) ?></td>
                            <td><?= htmlspecialchars($task['title']) ?></td>
                            <td><?= htmlspecialchars($task['detail']) ?></td>
                            <td><?= htmlspecialchars($task['due_date']) ?></td>
                            <td><?= htmlspecialchars($task['priority']) ?></td>
                            <td>
                                <a href="task_form.php?id=<?= $task['id'] ?>" class="edit-btn">編集</a>
                                <a href="delete_task.php?id=<?= $task['id'] ?>" class="delete-btn" onclick="return confirm('本当に削除しますか？');">削除</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html> 