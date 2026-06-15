<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM request WHERE user_id = ?");
$stmt->execute([$user_id]);
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Мои заявки</title>
</head>
<body>
<div class="page">
    <h1>Мои заявки</h1>
    <div class="links">
        <a href="create-request.php">Создать заявку</a>
        <a href="logout.php">Выйти из аккаунта</a>
    </div>

    <?php foreach ($requests as $request): ?>
        <div class="card">
            <p><b>Адрес:</b> <?= $request['address'] ?></p>
            <p><b>Контактные данные:</b> <?= $request['contacts'] ?></p>
            <p><b>Дата и время:</b> <?= $request['date_time'] ?></p>
            <p><b>Вид услуги:</b> <?= $request['service'] ?></p>
            <p><b>Тип оплаты:</b> <?= $request['payment'] ?></p>
            <p><b>Статус:</b> <?= $request['status'] ?></p>
            <?php if (!empty($request['cancel_reason'])): ?>
                <p><b>Причина отмены:</b> <?= $request['cancel_reason'] ?></p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>
