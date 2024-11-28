<?php
include('db_connect.php');
if (isset($_GET['delete_sotrudnik'])) {
    $id_to_delete = $_GET['delete_sotrudnik'];

    $stmt = $pdo->prepare("DELETE FROM сотрудники WHERE Идентификатор_сотрудника = ?");
    $stmt->execute([$id_to_delete]);

    header("Location: sotrudniki.php");
    exit();
}

$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

$sql_sotrudnik = "SELECT * FROM сотрудники WHERE Имя LIKE ? OR Фамилия LIKE ? OR Должность LIKE ? OR Email LIKE ? OR Телефон LIKE ? OR Отдел LIKE ?";
$searchParam = "%" . $search . "%";
$stmt = $pdo->prepare($sql_sotrudnik);
$stmt->execute([$searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
$sotrudniki = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_sotrudnik'])) {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $position = $_POST['position'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $hire_date = $_POST['hire_date'];
        $department = $_POST['department'];
        $active = $_POST['active'] ? 1 : 0;

        $sql = "INSERT INTO сотрудники (Имя, Фамилия, Должность, Email, Телефон, Дата_приёма, Отдел, Активен) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$first_name, $last_name, $position, $email, $phone, $hire_date, $department, $active]);
    } elseif (isset($_POST['update_sotrudnik'])) {
        $id = $_POST['id'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $position = $_POST['position'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $hire_date = $_POST['hire_date'];
        $department = $_POST['department'];
        $active = $_POST['active'] ? 1 : 0;

        $sql = "UPDATE сотрудники SET Имя = ?, Фамилия = ?, Должность = ?, Email = ?, Телефон = ?, Дата_приёма = ?, Отдел = ?, Активен = ? WHERE Идентификатор_сотрудника = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$first_name, $last_name, $position, $email, $phone, $hire_date, $department, $active, $id]);
    }
    header("Location: sotrudniki.php");
    exit();
}
$sotrudnik_to_edit = null;
if (isset($_GET['edit_sotrudnik'])) {
    $edit_sotrudnik_id = $_GET['edit_sotrudnik'];
    $stmt = $pdo->prepare("SELECT * FROM сотрудники WHERE Идентификатор_сотрудника = ?");
    $stmt->execute([$edit_sotrudnik_id]);
    $sotrudnik_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление сотрудниками</title>
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

        input[type="text"], input[type="email"], input[type="tel"], input[type="datetime-local"], select, button {
            padding: 12px;
            font-size: 1rem;
            border: 1px solid #ced4da;
            border-radius: 5px;
            width: 100%;
            margin-top: 5px;
            box-sizing: border-box;
        }

        input[type="text"]:focus, input[type="email"]:focus, input[type="tel"]:focus, input[type="datetime-local"]:focus, select:focus {
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
        <h1>Управление сотрудниками</h1>
        <h2>Добавить сотрудника</h2>
        <form method="POST">
            <div class="form-group">
                <label for="first_name">Имя:</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="last_name">Фамилия:</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="position">Должность:</label>
                <input type="text" id="position" name="position" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">Телефон:</label>
                <input type="tel" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="hire_date">Дата приёма:</label>
                <input type="datetime-local" id="hire_date" name="hire_date" required>
            </div>
            <div class="form-group">
                <label for="department">Отдел:</label>
                <input type="text" id="department" name="department" required>
            </div>
            <div class="form-group">
                <label for="active">Активен:</label>
                <select id="active" name="active">
                    <option value="1">Да</option>
                    <option value="0">Нет</option>
                </select>
            </div>
            <button type="submit" name="add_sotrudnik">Добавить сотрудника</button>
        </form>
        <h2>Поиск сотрудников</h2>
        <form method="GET">
            <div class="search-container">
                <input type="text" class="search-input" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Поиск по имени, фамилии, должности, email или телефону">
                <button type="submit" class="search-button">Найти</button>
            </div>
        </form>
        <div class="table-container">
            <h2>Список сотрудников</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Имя</th>
                        <th>Фамилия</th>
                        <th>Должность</th>
                        <th>Email</th>
                        <th>Телефон</th>
                        <th>Дата приёма</th>
                        <th>Отдел</th>
                        <th>Активен</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sotrudniki as $sotrudnik): ?>
                        <tr>
                            <td><?= htmlspecialchars($sotrudnik['Идентификатор_сотрудника']) ?></td>
                            <td><?= htmlspecialchars($sotrudnik['Имя']) ?></td>
                            <td><?= htmlspecialchars($sotrudnik['Фамилия']) ?></td>
                            <td><?= htmlspecialchars($sotrudnik['Должность']) ?></td>
                            <td><?= htmlspecialchars($sotrudnik['Email']) ?></td>
                            <td><?= htmlspecialchars($sotrudnik['Телефон']) ?></td>
                            <td><?= htmlspecialchars($sotrudnik['Дата_приёма']) ?></td>
                            <td><?= htmlspecialchars($sotrudnik['Отдел']) ?></td>
                            <td><?= $sotrudnik['Активен'] ? 'Да' : 'Нет' ?></td>
                            <td class="action-links">
                                <a href="?edit_sotrudnik=<?= $sotrudnik['Идентификатор_сотрудника'] ?>">Редактировать</a> | 
                                <a href="?delete_sotrudnik=<?= $sotrudnik['Идентификатор_сотрудника'] ?>" onclick="return confirm('Вы уверены?')">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($sotrudnik_to_edit): ?>
            <h2>Редактировать сотрудника</h2>
            <form method="POST">
                <input type="hidden" name="id" value="<?= $sotrudnik_to_edit['Идентификатор_сотрудника'] ?>">
                <div class="form-group">
                    <label for="first_name">Имя:</label>
                    <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($sotrudnik_to_edit['Имя']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Фамилия:</label>
                    <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($sotrudnik_to_edit['Фамилия']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="position">Должность:</label>
                    <input type="text" id="position" name="position" value="<?= htmlspecialchars($sotrudnik_to_edit['Должность']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($sotrudnik_to_edit['Email']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Телефон:</label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($sotrudnik_to_edit['Телефон']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="hire_date">Дата приёма:</label>
                    <input type="datetime-local" id="hire_date" name="hire_date" value="<?= htmlspecialchars($sotrudnik_to_edit['Дата_приёма']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="department">Отдел:</label>
                    <input type="text" id="department" name="department" value="<?= htmlspecialchars($sotrudnik_to_edit['Отдел']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="active">Активен:</label>
                    <select id="active" name="active">
                        <option value="1" <?= $sotrudnik_to_edit['Активен'] == 1 ? 'selected' : '' ?>>Да</option>
                        <option value="0" <?= $sotrudnik_to_edit['Активен'] == 0 ? 'selected' : '' ?>>Нет</option>
                    </select>
                </div>
                <button type="submit" name="update_sotrudnik">Обновить сотрудника</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
