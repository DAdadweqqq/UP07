<?php
include('db_connect.php');

if (isset($_GET['delete_vzaim'])) {
    $id_to_delete = $_GET['delete_vzaim'];

    $stmt = $pdo->prepare("DELETE FROM взаимодействия WHERE Идентификатор = ?");
    $stmt->execute([$id_to_delete]);

    header("Location: vzaim.php");
    exit();
}
$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

$sql_vzaim = "SELECT * FROM взаимодействия WHERE Пользователь_идентификатор LIKE ? OR Тип_взаимодействия LIKE ? OR Дата_взаимодействия LIKE ? OR Примечания LIKE ?";
$searchParam = "%" . $search . "%";
$stmt = $pdo->prepare($sql_vzaim);
$stmt->execute([$searchParam, $searchParam, $searchParam, $searchParam]);
$vzaim = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_vzaim'])) {
        $user_id = $_POST['user_id'];
        $interaction_type = $_POST['interaction_type'];
        $interaction_date = $_POST['interaction_date'];
        $notes = $_POST['notes'];

        $sql = "INSERT INTO взаимодействия (Пользователь_идентификатор, Тип_взаимодействия, Дата_взаимодействия, Примечания) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $interaction_type, $interaction_date, $notes]);
    } elseif (isset($_POST['update_vzaim'])) {
        $id = $_POST['id'];
        $user_id = $_POST['user_id'];
        $interaction_type = $_POST['interaction_type'];
        $interaction_date = $_POST['interaction_date'];
        $notes = $_POST['notes'];

        $sql = "UPDATE взаимодействия SET Пользователь_идентификатор = ?, Тип_взаимодействия = ?, Дата_взаимодействия = ?, Примечания = ? WHERE Идентификатор = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $interaction_type, $interaction_date, $notes, $id]);
    }
    header("Location: vzaim.php");
    exit();
}
$vzaim_to_edit = null;
if (isset($_GET['edit_vzaim'])) {
    $edit_vzaim_id = $_GET['edit_vzaim'];
    $stmt = $pdo->prepare("SELECT * FROM взаимодействия WHERE Идентификатор = ?");
    $stmt->execute([$edit_vzaim_id]);
    $vzaim_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление взаимодействиями</title>
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

        input[type="text"], input[type="datetime-local"], button {
            padding: 12px;
            font-size: 1rem;
            border: 1px solid #ced4da;
            border-radius: 5px;
            width: 100%;
            margin-top: 5px;
            box-sizing: border-box;
        }

        input[type="text"]:focus, input[type="datetime-local"]:focus {
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

        <h1>Управление взаимодействиями</h1>
        <h2>Добавить новое взаимодействие</h2>
        <form method="POST">
            <div class="form-group">
                <label for="user_id">Пользователь ID:</label>
                <input type="text" id="user_id" name="user_id" required>
            </div>
            <div class="form-group">
                <label for="interaction_type">Тип взаимодействия:</label>
                <input type="text" id="interaction_type" name="interaction_type" required>
            </div>
            <div class="form-group">
                <label for="interaction_date">Дата взаимодействия:</label>
                <input type="datetime-local" id="interaction_date" name="interaction_date" required>
            </div>
            <div class="form-group">
                <label for="notes">Примечания:</label>
                <input type="text" id="notes" name="notes">
            </div>
            <button type="submit" name="add_vzaim">Добавить взаимодействие</button>
        </form>
        <h2>Поиск взаимодействий</h2>
        <form method="GET">
            <div class="search-container">
                <input type="text" class="search-input" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Поиск по ID, типу, дате или примечаниям">
                <button type="submit" class="search-button">Найти</button>
            </div>
        </form>
        <div class="table-container">
            <h2>Список взаимодействий</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Пользователь ID</th>
                        <th>Тип взаимодействия</th>
                        <th>Дата взаимодействия</th>
                        <th>Примечания</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vzaim as $interaction): ?>
                        <tr>
                            <td><?= htmlspecialchars($interaction['Идентификатор']) ?></td>
                            <td><?= htmlspecialchars($interaction['Пользователь_идентификатор']) ?></td>
                            <td><?= htmlspecialchars($interaction['Тип_взаимодействия']) ?></td>
                            <td><?= htmlspecialchars($interaction['Дата_взаимодействия']) ?></td>
                            <td><?= htmlspecialchars($interaction['Примечания']) ?></td>
                            <td class="action-links">
                                <a href="?edit_vzaim=<?= $interaction['Идентификатор'] ?>">Редактировать</a> | 
                                <a href="?delete_vzaim=<?= $interaction['Идентификатор'] ?>" onclick="return confirm('Вы уверены?')">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($vzaim_to_edit): ?>
            <h2>Редактировать взаимодействие</h2>
            <form method="POST">
                <input type="hidden" name="id" value="<?= $vzaim_to_edit['Идентификатор'] ?>">
                <div class="form-group">
                    <label for="user_id">Пользователь ID:</label>
                    <input type="text" id="user_id" name="user_id" value="<?= htmlspecialchars($vzaim_to_edit['Пользователь_идентификатор']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="interaction_type">Тип взаимодействия:</label>
                    <input type="text" id="interaction_type" name="interaction_type" value="<?= htmlspecialchars($vzaim_to_edit['Тип_взаимодействия']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="interaction_date">Дата взаимодействия:</label>
                    <input type="datetime-local" id="interaction_date" name="interaction_date" value="<?= htmlspecialchars($vzaim_to_edit['Дата_взаимодействия']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="notes">Примечания:</label>
                    <input type="text" id="notes" name="notes" value="<?= htmlspecialchars($vzaim_to_edit['Примечания']) ?>">
                </div>
                <button type="submit" name="update_vzaim">Обновить данные</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
