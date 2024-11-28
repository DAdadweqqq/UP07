<?php
include('db_connect.php');
if (isset($_GET['delete_zayavka'])) {
    $id_to_delete = $_GET['delete_zayavka'];

    $stmt = $pdo->prepare("DELETE FROM заявки WHERE Идентификатор = ?");
    $stmt->execute([$id_to_delete]);

    header("Location: zayavki.php");
    exit();
}

$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

$sql_zayavka = "SELECT * FROM заявки WHERE Пользователь_идентификатор LIKE ? OR Имя LIKE ? OR Фамилия LIKE ? OR Название_компании LIKE ? OR Тип_проекта LIKE ? OR Описание_задачи LIKE ?";
$searchParam = "%" . $search . "%";
$stmt = $pdo->prepare($sql_zayavka);
$stmt->execute([$searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
$zayavki = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_zayavka'])) {
        $user_id = $_POST['user_id'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $company_name = $_POST['company_name'];
        $project_type = $_POST['project_type'];
        $task_description = $_POST['task_description'];
        $start_date = $_POST['start_date'];
        $additional_info = $_POST['additional_info'];
        $contact_info = $_POST['contact_info'];
        $budget = $_POST['budget'];

        $sql = "INSERT INTO заявки (Пользователь_идентификатор, Имя, Фамилия, Название_компании, Тип_проекта, Описание_задачи, Дата_запуска, Дополнительная_информация, Контактная_информация, Бюджет) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $first_name, $last_name, $company_name, $project_type, $task_description, $start_date, $additional_info, $contact_info, $budget]);
    } elseif (isset($_POST['update_zayavka'])) {
        $id = $_POST['id'];
        $user_id = $_POST['user_id'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $company_name = $_POST['company_name'];
        $project_type = $_POST['project_type'];
        $task_description = $_POST['task_description'];
        $start_date = $_POST['start_date'];
        $additional_info = $_POST['additional_info'];
        $contact_info = $_POST['contact_info'];
        $budget = $_POST['budget'];

        $sql = "UPDATE заявки SET Пользователь_идентификатор = ?, Имя = ?, Фамилия = ?, Название_компании = ?, Тип_проекта = ?, Описание_задачи = ?, Дата_запуска = ?, Дополнительная_информация = ?, Контактная_информация = ?, Бюджет = ? WHERE Идентификатор = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $first_name, $last_name, $company_name, $project_type, $task_description, $start_date, $additional_info, $contact_info, $budget, $id]);
    }
    header("Location: zayavki.php");
    exit();
}
$zayavka_to_edit = null;
if (isset($_GET['edit_zayavka'])) {
    $edit_zayavka_id = $_GET['edit_zayavka'];
    $stmt = $pdo->prepare("SELECT * FROM заявки WHERE Идентификатор = ?");
    $stmt->execute([$edit_zayavka_id]);
    $zayavka_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление заявками</title>
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
        }

        .search-button:hover {
            background-color: #218838;
        }

    </style>
</head>
<body>
    <div class="container">
        <button class="back-button" onclick="window.location.href='index.html'">На главную</button>
        <h1>Управление заявками</h1>
        <h2>Добавить заявку</h2>
        <form method="POST">
            <div class="form-group">
                <label for="user_id">Пользователь ID:</label>
                <input type="text" id="user_id" name="user_id" required>
            </div>
            <div class="form-group">
                <label for="first_name">Имя:</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="last_name">Фамилия:</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="company_name">Название компании:</label>
                <input type="text" id="company_name" name="company_name" required>
            </div>
            <div class="form-group">
                <label for="project_type">Тип проекта:</label>
                <input type="text" id="project_type" name="project_type" required>
            </div>
            <div class="form-group">
                <label for="task_description">Описание задачи:</label>
                <input type="text" id="task_description" name="task_description" required>
            </div>
            <div class="form-group">
                <label for="start_date">Дата запуска:</label>
                <input type="datetime-local" id="start_date" name="start_date" required>
            </div>
            <div class="form-group">
                <label for="additional_info">Дополнительная информация:</label>
                <input type="text" id="additional_info" name="additional_info" required>
            </div>
            <div class="form-group">
                <label for="contact_info">Контактная информация:</label>
                <input type="text" id="contact_info" name="contact_info" required>
            </div>
            <div class="form-group">
                <label for="budget">Бюджет:</label>
                <input type="text" id="budget" name="budget" required>
            </div>
            <button type="submit" name="add_zayavka">Добавить заявку</button>
        </form>
        <h2>Поиск заявок</h2>
        <form method="GET">
            <div class="search-container">
                <input type="text" class="search-input" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Поиск по ID, имени, фамилии или названию компании">
                <button type="submit" class="search-button">Найти</button>
            </div>
        </form>
        <div class="table-container">
            <h2>Список заявок</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Пользователь ID</th>
                        <th>Имя</th>
                        <th>Фамилия</th>
                        <th>Название компании</th>
                        <th>Тип проекта</th>
                        <th>Описание задачи</th>
                        <th>Дата запуска</th>
                        <th>Доп. информация</th>
                        <th>Контактная информация</th>
                        <th>Бюджет</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($zayavki as $zayavka): ?>
                        <tr>
                            <td><?= htmlspecialchars($zayavka['Идентификатор']) ?></td>
                            <td><?= htmlspecialchars($zayavka['Пользователь_идентификатор']) ?></td>
                            <td><?= htmlspecialchars($zayavka['Имя']) ?></td>
                            <td><?= htmlspecialchars($zayavka['Фамилия']) ?></td>
                            <td><?= htmlspecialchars($zayavka['Название_компании']) ?></td>
                            <td><?= htmlspecialchars($zayavka['Тип_проекта']) ?></td>
                            <td><?= htmlspecialchars($zayavka['Описание_задачи']) ?></td>
                            <td><?= htmlspecialchars($zayavka['Дата_запуска']) ?></td>
                            <td><?= htmlspecialchars($zayavka['Дополнительная_информация']) ?></td>
                            <td><?= htmlspecialchars($zayavka['Контактная_информация']) ?></td>
                            <td><?= htmlspecialchars($zayavka['Бюджет']) ?></td>
                            <td class="action-links">
                                <a href="?edit_zayavka=<?= $zayavka['Идентификатор'] ?>">Редактировать</a> | 
                                <a href="?delete_zayavka=<?= $zayavka['Идентификатор'] ?>" onclick="return confirm('Вы уверены?')">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($zayavka_to_edit): ?>
            <h2>Редактировать заявку</h2>
            <form method="POST">
                <input type="hidden" name="id" value="<?= $zayavka_to_edit['Идентификатор'] ?>">
                <div class="form-group">
                    <label for="user_id">Пользователь ID:</label>
                    <input type="text" id="user_id" name="user_id" value="<?= htmlspecialchars($zayavka_to_edit['Пользователь_идентификатор']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="first_name">Имя:</label>
                    <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($zayavka_to_edit['Имя']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Фамилия:</label>
                    <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($zayavka_to_edit['Фамилия']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="company_name">Название компании:</label>
                    <input type="text" id="company_name" name="company_name" value="<?= htmlspecialchars($zayavka_to_edit['Название_компании']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="project_type">Тип проекта:</label>
                    <input type="text" id="project_type" name="project_type" value="<?= htmlspecialchars($zayavka_to_edit['Тип_проекта']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="task_description">Описание задачи:</label>
                    <input type="text" id="task_description" name="task_description" value="<?= htmlspecialchars($zayavka_to_edit['Описание_задачи']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="start_date">Дата запуска:</label>
                    <input type="datetime-local" id="start_date" name="start_date" value="<?= htmlspecialchars($zayavka_to_edit['Дата_запуска']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="additional_info">Дополнительная информация:</label>
                    <input type="text" id="additional_info" name="additional_info" value="<?= htmlspecialchars($zayavka_to_edit['Дополнительная_информация']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="contact_info">Контактная информация:</label>
                    <input type="text" id="contact_info" name="contact_info" value="<?= htmlspecialchars($zayavka_to_edit['Контактная_информация']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="budget">Бюджет:</label>
                    <input type="text" id="budget" name="budget" value="<?= htmlspecialchars($zayavka_to_edit['Бюджет']) ?>" required>
                </div>
                <button type="submit" name="update_zayavka">Обновить заявку</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
