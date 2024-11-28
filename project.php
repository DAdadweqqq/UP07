<?php
include('db_connect.php'); 
if (isset($_GET['delete_project'])) {
    $id_to_delete = $_GET['delete_project'];

    $stmt = $pdo->prepare("DELETE FROM проекты WHERE Идентификатор = ?");
    $stmt->execute([$id_to_delete]);

    header("Location: project.php"); 
    exit(); 
}
$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

$sql_projects = "SELECT * FROM проекты WHERE Заявка_идентификатор LIKE ? OR Название_проекта LIKE ? OR Статус_проекта LIKE ? OR Дата_старта LIKE ? OR Дата_окончания LIKE ?";
$searchParam = "%" . $search . "%";
$stmt = $pdo->prepare($sql_projects);
$stmt->execute([$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_project'])) {
        $application_id = $_POST['application_id'];
        $project_name = $_POST['project_name'];
        $status = $_POST['status'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $client_feedback = $_POST['client_feedback'];
        $manager_id = $_POST['manager_id'];
        $sql = "INSERT INTO проекты (Заявка_идентификатор, Название_проекта, Статус_проекта, Дата_старта, Дата_окончания, Отзывы_клиента, Менеджер_проекта_идентификатор) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$application_id, $project_name, $status, $start_date, $end_date, $client_feedback, $manager_id]);
    } elseif (isset($_POST['update_project'])) {
        $id = $_POST['id'];
        $application_id = $_POST['application_id'];
        $project_name = $_POST['project_name'];
        $status = $_POST['status'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $client_feedback = $_POST['client_feedback'];
        $manager_id = $_POST['manager_id'];
        $sql = "UPDATE проекты SET Заявка_идентификатор = ?, Название_проекта = ?, Статус_проекта = ?, Дата_старта = ?, Дата_окончания = ?, Отзывы_клиента = ?, Менеджер_проекта_идентификатор = ? WHERE Идентификатор = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$application_id, $project_name, $status, $start_date, $end_date, $client_feedback, $manager_id, $id]);
    }
    header("Location: project.php"); 
    exit(); 
}
$project_to_edit = null;
if (isset($_GET['edit_project'])) {
    $edit_project_id = $_GET['edit_project'];
    $stmt = $pdo->prepare("SELECT * FROM проекты WHERE Идентификатор = ?");
    $stmt->execute([$edit_project_id]);
    $project_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление проектами</title>
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

        input[type="text"], input[type="datetime-local"], input[type="number"], button {
            padding: 12px;
            font-size: 1rem;
            border: 1px solid #ced4da;
            border-radius: 5px;
            width: 100%;
            margin-top: 5px;
            box-sizing: border-box;
        }

        .back-button {
            background-color: #007bff;
            color: white;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            padding: 8px 16px;
            position: absolute;
            top: 40px;
            left: 150px;
            border-radius: 5px;
            z-index: 1000;
            width: 200px;
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
        <h1>Управление проектами</h1>
        <h2>Добавить новый проект</h2>
        <form method="POST">
            <div class="form-group">
                <label for="application_id">Заявка ID:</label>
                <input type="text" id="application_id" name="application_id" required>
            </div>
            <div class="form-group">
                <label for="project_name">Название проекта:</label>
                <input type="text" id="project_name" name="project_name" required>
            </div>
            <div class="form-group">
                <label for="status">Статус проекта:</label>
                <input type="text" id="status" name="status" required>
            </div>
            <div class="form-group">
                <label for="start_date">Дата старта:</label>
                <input type="datetime-local" id="start_date" name="start_date" required>
            </div>
            <div class="form-group">
                <label for="end_date">Дата окончания:</label>
                <input type="datetime-local" id="end_date" name="end_date" required>
            </div>
            <div class="form-group">
                <label for="client_feedback">Отзывы клиента:</label>
                <input type="text" id="client_feedback" name="client_feedback" required>
            </div>
            <div class="form-group">
                <label for="manager_id">Менеджер ID:</label>
                <input type="text" id="manager_id" name="manager_id" required>
            </div>
            <button type="submit" name="add_project">Добавить проект</button>
        </form>

        <h2>Поиск проектов</h2>
        <form method="GET">
            <div class="search-container">
                <input type="text" class="search-input" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Поиск по заявке, названию, статусу или датам">
                <button type="submit" class="search-button">Найти</button>
            </div>
        </form>

        <div class="table-container">
            <h2>Список проектов</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Заявка ID</th>
                        <th>Название проекта</th>
                        <th>Статус проекта</th>
                        <th>Дата старта</th>
                        <th>Дата окончания</th>
                        <th>Отзывы клиента</th>
                        <th>Менеджер ID</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                        <tr>
                            <td><?= htmlspecialchars($project['Идентификатор']) ?></td>
                            <td><?= htmlspecialchars($project['Заявка_идентификатор']) ?></td>
                            <td><?= htmlspecialchars($project['Название_проекта']) ?></td>
                            <td><?= htmlspecialchars($project['Статус_проекта']) ?></td>
                            <td><?= htmlspecialchars($project['Дата_старта']) ?></td>
                            <td><?= htmlspecialchars($project['Дата_окончания']) ?></td>
                            <td><?= htmlspecialchars($project['Отзывы_клиента']) ?></td>
                            <td><?= htmlspecialchars($project['Менеджер_проекта_идентификатор']) ?></td>
                            <td class="action-links">
                                <a href="?edit_project=<?= $project['Идентификатор'] ?>">Редактировать</a> | 
                                <a href="?delete_project=<?= $project['Идентификатор'] ?>" onclick="return confirm('Вы уверены?')">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($project_to_edit): ?>
            <h2>Редактировать проект</h2>
            <form method="POST">
                <input type="hidden" name="id" value="<?= $project_to_edit['Идентификатор'] ?>">
                <div class="form-group">
                    <label for="application_id">Заявка ID:</label>
                    <input type="text" id="application_id" name="application_id" value="<?= htmlspecialchars($project_to_edit['Заявка_идентификатор']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="project_name">Название проекта:</label>
                    <input type="text" id="project_name" name="project_name" value="<?= htmlspecialchars($project_to_edit['Название_проекта']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="status">Статус проекта:</label>
                    <input type="text" id="status" name="status" value="<?= htmlspecialchars($project_to_edit['Статус_проекта']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="start_date">Дата старта:</label>
                    <input type="datetime-local" id="start_date" name="start_date" value="<?= htmlspecialchars($project_to_edit['Дата_старта']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="end_date">Дата окончания:</label>
                    <input type="datetime-local" id="end_date" name="end_date" value="<?= htmlspecialchars($project_to_edit['Дата_окончания']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="client_feedback">Отзывы клиента:</label>
                    <input type="text" id="client_feedback" name="client_feedback" value="<?= htmlspecialchars($project_to_edit['Отзывы_клиента']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="manager_id">Менеджер ID:</label>
                    <input type="text" id="manager_id" name="manager_id" value="<?= htmlspecialchars($project_to_edit['Менеджер_проекта_идентификатор']) ?>" required>
                </div>
                <button type="submit" name="update_project">Обновить данные</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
