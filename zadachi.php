<?php
include('db_connect.php');
if (isset($_GET['delete_zadacha'])) {
    $id_to_delete = $_GET['delete_zadacha'];

    $stmt = $pdo->prepare("DELETE FROM задачи WHERE Идентификатор = ?");
    $stmt->execute([$id_to_delete]);

    header("Location: zadachi.php");
    exit();
}
$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

$sql_zadacha = "SELECT * FROM задачи WHERE Проект_идентификатор LIKE ? OR Название_задачи LIKE ? OR Исполнитель_идентификатор LIKE ? OR Статус_задачи LIKE ?";
$searchParam = "%" . $search . "%";
$stmt = $pdo->prepare($sql_zadacha);
$stmt->execute([$searchParam, $searchParam, $searchParam, $searchParam]);
$zadachi = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_zadacha'])) {
        $project_id = $_POST['project_id'];
        $task_title = $_POST['task_title'];
        $task_description = $_POST['task_description'];
        $executor_id = $_POST['executor_id'];
        $due_date = $_POST['due_date'];
        $task_status = $_POST['task_status'];

        $sql = "INSERT INTO задачи (Проект_идентификатор, Название_задачи, Описание_задачи, Исполнитель_идентификатор, Дата_выполнения, Статус_задачи) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$project_id, $task_title, $task_description, $executor_id, $due_date, $task_status]);
    } elseif (isset($_POST['update_zadacha'])) {
        $id = $_POST['id'];
        $project_id = $_POST['project_id'];
        $task_title = $_POST['task_title'];
        $task_description = $_POST['task_description'];
        $executor_id = $_POST['executor_id'];
        $due_date = $_POST['due_date'];
        $task_status = $_POST['task_status'];

        $sql = "UPDATE задачи SET Проект_идентификатор = ?, Название_задачи = ?, Описание_задачи = ?, Исполнитель_идентификатор = ?, Дата_выполнения = ?, Статус_задачи = ? WHERE Идентификатор = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$project_id, $task_title, $task_description, $executor_id, $due_date, $task_status, $id]);
    }
    header("Location: zadachi.php");
    exit();
}
$zadacha_to_edit = null;
if (isset($_GET['edit_zadacha'])) {
    $edit_zadacha_id = $_GET['edit_zadacha'];
    $stmt = $pdo->prepare("SELECT * FROM задачи WHERE Идентификатор = ?");
    $stmt->execute([$edit_zadacha_id]);
    $zadacha_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление задачами</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .container {
            width: 80%;
            margin: 30px auto;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
            position: relative;
        }

 
        h1 {
            font-size: 2rem;
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        h2 {
            font-size: 1.5rem;
            color: #495057;
            margin-bottom: 15px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
        }
        .back-button {
            background-color: #007bff;
            color: white;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            padding: 8px 16px;
            position: absolute;
            top: 10px;
            left: 10px;
            border-radius: 5px;
            z-index: 1000;
            width: 200px;
        }

        .back-button:hover {
            background-color: #0056b3;
        }
        .form-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 1rem;
            color: #333;
            margin-bottom: 8px;
        }

        input[type="text"], input[type="datetime-local"], select, button {
            padding: 12px;
            font-size: 1rem;
            border: 1px solid #ced4da;
            border-radius: 5px;
            width: 100%;
            margin-top: 5px;
            box-sizing: border-box;
        }

        input[type="text"]:focus, input[type="datetime-local"]:focus, select:focus {
            outline: none;
            border-color: #007bff;
        }

        button {
            background-color: #007bff;
            color: white;
            font-size: 1.1rem;
            border: none;
            cursor: pointer;
            padding: 12px;
            transition: background-color 0.3s;
            width: 100%;
            margin-top: 15px;
            border-radius: 5px;
        }

        button:hover {
            background-color: #0056b3;
        }

        button:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.5);
        }

/
        .table-container {
            margin-top: 30px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: bold;
        }

        table td {
            font-size: 1rem;
            color: #495057;
        }

        .action-links a {
            color: #dc3545;
            text-decoration: none;
            font-weight: bold;
        }

        .action-links a:hover {
            text-decoration: underline;
        }
        .search-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .search-input {
            width: 80%;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ced4da;
            border-radius: 5px;
        }

        .search-button {
            padding: 10px 15px;
            font-size: 1rem;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 18%;
        }

        .search-button:hover {
            background-color: #218838;
        }

        @media (max-width: 768px) {
            .container {
                width: 95%;
            }

            .search-input, .search-button {
                width: 100%;
            }

            .search-container {
                flex-direction: column;
            }

            .search-button {
                margin-top: 10px;
            }
        }

    </style>
</head>
<body>
    <div class="container">
        <form action="index.html">
            <button type="submit" class="back-button">На главную</button>
        </form>

        <h1>Управление задачами</h1>
        <h2>Добавить задачу</h2>
        <form method="POST">
            <div class="form-group">
                <label for="project_id">Проект ID:</label>
                <input type="text" id="project_id" name="project_id" required>
            </div>
            <div class="form-group">
                <label for="task_title">Название задачи:</label>
                <input type="text" id="task_title" name="task_title" required>
            </div>
            <div class="form-group">
                <label for="task_description">Описание задачи:</label>
                <input type="text" id="task_description" name="task_description" required>
            </div>
            <div class="form-group">
                <label for="executor_id">Исполнитель ID:</label>
                <input type="text" id="executor_id" name="executor_id" required>
            </div>
            <div class="form-group">
                <label for="due_date">Дата выполнения:</label>
                <input type="datetime-local" id="due_date" name="due_date" required>
            </div>
            <div class="form-group">
                <label for="task_status">Статус задачи:</label>
                <input type="text" id="task_status" name="task_status" required>
            </div>
            <button type="submit" name="add_zadacha">Добавить задачу</button>
        </form>
        <h2>Поиск задач</h2>
        <form method="GET">
            <div class="search-container">
                <input type="text" class="search-input" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Поиск по ID, названию или статусу">
                <button type="submit" class="search-button">Найти</button>
            </div>
        </form>
        <div class="table-container">
            <h2>Список задач</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Проект ID</th>
                        <th>Название задачи</th>
                        <th>Описание задачи</th>
                        <th>Исполнитель ID</th>
                        <th>Дата выполнения</th>
                        <th>Статус задачи</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($zadachi as $task): ?>
                        <tr>
                            <td><?= htmlspecialchars($task['Идентификатор']) ?></td>
                            <td><?= htmlspecialchars($task['Проект_идентификатор']) ?></td>
                            <td><?= htmlspecialchars($task['Название_задачи']) ?></td>
                            <td><?= htmlspecialchars($task['Описание_задачи']) ?></td>
                            <td><?= htmlspecialchars($task['Исполнитель_идентификатор']) ?></td>
                            <td><?= htmlspecialchars($task['Дата_выполнения']) ?></td>
                            <td><?= htmlspecialchars($task['Статус_задачи']) ?></td>
                            <td class="action-links">
                                <a href="?edit_zadacha=<?= $task['Идентификатор'] ?>">Редактировать</a> | 
                                <a href="?delete_zadacha=<?= $task['Идентификатор'] ?>" onclick="return confirm('Вы уверены?')">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($zadacha_to_edit): ?>
            <h2>Редактировать задачу</h2>
            <form method="POST">
                <input type="hidden" name="id" value="<?= $zadacha_to_edit['Идентификатор'] ?>">
                <div class="form-group">
                    <label for="project_id">Проект ID:</label>
                    <input type="text" id="project_id" name="project_id" value="<?= htmlspecialchars($zadacha_to_edit['Проект_идентификатор']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="task_title">Название задачи:</label>
                    <input type="text" id="task_title" name="task_title" value="<?= htmlspecialchars($zadacha_to_edit['Название_задачи']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="task_description">Описание задачи:</label>
                    <input type="text" id="task_description" name="task_description" value="<?= htmlspecialchars($zadacha_to_edit['Описание_задачи']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="executor_id">Исполнитель ID:</label>
                    <input type="text" id="executor_id" name="executor_id" value="<?= htmlspecialchars($zadacha_to_edit['Исполнитель_идентификатор']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="due_date">Дата выполнения:</label>
                    <input type="datetime-local" id="due_date" name="due_date" value="<?= htmlspecialchars($zadacha_to_edit['Дата_выполнения']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="task_status">Статус задачи:</label>
                    <input type="text" id="task_status" name="task_status" value="<?= htmlspecialchars($zadacha_to_edit['Статус_задачи']) ?>" required>
                </div>
                <button type="submit" name="update_zadacha">Обновить задачу</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
