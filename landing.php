<?php
include('db_connect.php'); 

if (isset($_GET['delete_user'])) {
    $id_to_delete = $_GET['delete_user'];

    $stmt = $pdo->prepare("DELETE FROM пользователи WHERE Идентификатор_пользователя = ?");
    $stmt->execute([$id_to_delete]);

    header("Location: landing.php"); 
    exit(); 
}

$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

$sql_users = "SELECT * FROM пользователи WHERE ip_address LIKE ? OR Идентификатор_пользователя LIKE ? OR created_at LIKE ?";
$searchParam = "%" . $search . "%";
$stmt = $pdo->prepare($sql_users);
$stmt->execute([$searchParam, $searchParam, $searchParam]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_user'])) {
        $ip_address = $_POST['ip_address'];
        $created_at = $_POST['created_at'];
        $sql = "INSERT INTO пользователи (ip_address, created_at) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ip_address, $created_at]);
    } elseif (isset($_POST['update_user'])) {
        $id = $_POST['id'];
        $ip_address = $_POST['ip_address'];
        $created_at = $_POST['created_at'];
        $sql = "UPDATE пользователи SET ip_address = ?, created_at = ? WHERE Идентификатор_пользователя = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ip_address, $created_at, $id]);
    }
    header("Location: landing.php"); 
    exit(); 
}

$user_to_edit = null;
if (isset($_GET['edit_user'])) {
    $edit_user_id = $_GET['edit_user'];
    $stmt = $pdo->prepare("SELECT * FROM пользователи WHERE Идентификатор_пользователя = ?");
    $stmt->execute([$edit_user_id]);
    $user_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление данными</title>
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

        <h1>Управление данными пользователей</h1>
        <h2>Добавить нового пользователя</h2>
        <form method="POST">
            <div class="form-group">
                <label for="ip_address">IP-адрес:</label>
                <input type="text" id="ip_address" name="ip_address" required>
            </div>
            <div class="form-group">
                <label for="created_at">Дата создания:</label>
                <input type="datetime-local" id="created_at" name="created_at" required>
            </div>
            <button type="submit" name="add_user">Добавить пользователя</button>
        </form>
        <h2>Поиск пользователей</h2>
        <form method="GET">
            <div class="search-container">
                <input type="text" class="search-input" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Поиск по IP, ID или дате">
                <button type="submit" class="search-button">Найти</button>
            </div>
        </form>
        <div class="table-container">
            <h2>Список пользователей</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>IP-адрес</th>
                        <th>Дата создания</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['Идентификатор_пользователя']) ?></td>
                            <td><?= htmlspecialchars($user['ip_address']) ?></td>
                            <td><?= htmlspecialchars($user['created_at']) ?></td>
                            <td class="action-links">
                                <a href="?edit_user=<?= $user['Идентификатор_пользователя'] ?>">Редактировать</a> | 
                                <a href="?delete_user=<?= $user['Идентификатор_пользователя'] ?>" onclick="return confirm('Вы уверены?')">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($user_to_edit): ?>
            <h2>Редактировать пользователя</h2>
            <form method="POST">
                <input type="hidden" name="id" value="<?= $user_to_edit['Идентификатор_пользователя'] ?>">
                <div class="form-group">
                    <label for="ip_address">IP-адрес:</label>
                    <input type="text" id="ip_address" name="ip_address" value="<?= htmlspecialchars($user_to_edit['ip_address']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="created_at">Дата создания:</label>
                    <input type="datetime-local" id="created_at" name="created_at" value="<?= htmlspecialchars($user_to_edit['created_at']) ?>" required>
                </div>
                <button type="submit" name="update_user">Обновить данные</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
