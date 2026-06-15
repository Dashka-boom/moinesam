<?php
session_start();
require "db.php";

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $cancel_reason = $_POST['cancel_reason'];

    $stmt = $pdo->prepare("
        UPDATE request 
        SET status = ?, cancel_reason = ?
        WHERE id = ?
    ");

    $stmt->execute([$status, $cancel_reason, $id]);

    header('Location: admin.php');
    exit;
}

$stmt = $pdo->query("SELECT request.*, users.name, users.surname, users.middle_name, users.phone, users.email FROM request JOIN users ON request.user_id = users.id");
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Панель администратора</title>
</head>
<body>
<div class="page">
    <h1>Панель администратора</h1>
    <a href="logout.php">Выйти</a>

    <?php foreach ($requests as $request): ?>
        <div class="card">
            <p><b>ФИО:</b> <?= $request['surname'] ?> <?= $request['name'] ?> <?= $request['middle_name'] ?></p>
            <p><b>Телефон:</b> <?= $request['phone'] ?></p>
            <p><b>Email:</b> <?= $request['email'] ?></p>
            <p><b>Адрес:</b> <?= $request['address'] ?></p>
            <p><b>Контактные данные:</b> <?= $request['contacts'] ?></p>
            <p><b>Дата и время:</b> <?= $request['date_time'] ?></p>
            <p><b>Услуга:</b> <?= $request['service'] ?></p>
            <p><b>Оплата:</b> <?= $request['payment'] ?></p>
            <p><b>Статус:</b> <?= $request['status'] ?></p>
            <?php if (!empty($request['cancel_reason'])): ?>
                <p><b>Причина отмены:</b> <?= $request['cancel_reason'] ?></p>
            <?php endif; ?>
         <form method="POST">
                <input type="hidden" name="id" value="<?= $request['id'] ?>">

                <select name="status" required>
                    <option value="Новая заявка">Новая заявка</option>
                    <option value="В работе">В работе</option>
                    <option value="Выполнено">Выполнено</option>
                    <option value="Отменено">Отменено</option>
                </select>

                <input type="text" name="cancel_reason" placeholder="Причина отмены">

                <button type="submit">Сохранить</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>
