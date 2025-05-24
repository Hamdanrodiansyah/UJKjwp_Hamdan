<?php
$dataFile = 'data.json';
$tasks = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];

// Tambah tugas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'create') {
    $newTask = [
        'id' => uniqid(),
        'text' => htmlspecialchars($_POST['task']),
        'done' => false
    ];
    $tasks[] = $newTask;
    file_put_contents($dataFile, json_encode($tasks, JSON_PRETTY_PRINT));
    header('Location: index.php');
    exit();
}

// Update status selesai dari checkbox
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'toggle') {
    foreach ($tasks as &$task) {
        if ($task['id'] === $_POST['id']) {
            $task['done'] = $_POST['done'] === '1';
        }
    }
    file_put_contents($dataFile, json_encode($tasks, JSON_PRETTY_PRINT));
    header('Location: index.php');
    exit();
}

// Hapus tugas
if (isset($_GET['delete'])) {
    $tasks = array_filter($tasks, fn($task) => $task['id'] !== $_GET['delete']);
    file_put_contents($dataFile, json_encode(array_values($tasks), JSON_PRETTY_PRINT));
    header('Location: index.php');
    exit();
}

// Form edit
$editTask = null;
if (isset($_GET['edit'])) {
    foreach ($tasks as $task) {
        if ($task['id'] === $_GET['edit']) {
            $editTask = $task;
            break;
        }
    }
}

// Proses update teks
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update') {
    foreach ($tasks as &$task) {
        if ($task['id'] === $_POST['id']) {
            $task['text'] = htmlspecialchars($_POST['task']);
        }
    }
    file_put_contents($dataFile, json_encode($tasks, JSON_PRETTY_PRINT));
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>To-Do List</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>To-Do List</h1>
    </header>

    <main>
        <?php if ($editTask): ?>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= $editTask['id'] ?>">
                <input type="text" name="task" value="<?= $editTask['text'] ?>" required>
                <button type="submit">Simpan</button>
                <a href="index.php">Batal</a>
            </form>
        <?php else: ?>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <input type="text" name="task" placeholder="Tambah tugas..." required>
                <button type="submit">Tambah</button>
            </form>
        <?php endif; ?>

        <ul class="task-list">
            <?php foreach ($tasks as $task): ?>
                <li class="<?= $task['done'] ? 'done' : '' ?>">
                    <form method="POST" class="checkbox-form">
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="id" value="<?= $task['id'] ?>">
                        <input type="hidden" name="done" value="<?= $task['done'] ? '0' : '1' ?>">
                        <input type="checkbox" onchange="this.form.submit()" <?= $task['done'] ? 'checked' : '' ?>>
                        <span><?= $task['text'] ?></span>
                    </form>
                    <div class="actions">
                        <a href="?edit=<?= $task['id'] ?>">Edit</a>
                        <a href="?delete=<?= $task['id'] ?>" class="delete">Hapus</a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </main>
</body>
</html>
