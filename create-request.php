<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address = $_POST['address'];
    $contacts = $_POST['contacts'];
    $date_time = $_POST['date_time'];
    $service = $_POST['service'];
    $payment = $_POST['payment'];

    $stmt = $pdo->prepare("INSERT INTO request (user_id, address, contacts, date_time, service, payment, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $address, $contacts, $date_time, $service, $payment, 'Новая заявка']);

    header('Location: request.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Создание заявки</title>
</head>
<body>
<div class="login">
    <form method="POST">
        <h1>Создание заявки</h1>
        <input type="text" name="address" placeholder="Адрес" required>
        <input type="text" name="contacts" placeholder="Контактные данные" required>
        <input type="datetime-local" name="date_time" required>

        <select name="service" required>
            <option value="Общий клининг">Общий клининг</option>
            <option value="Генеральная уборка">Генеральная уборка</option>
            <option value="Послестроительная уборка">Послестроительная уборка</option>
            <option value="Химчистка ковров и мебели">Химчистка ковров и мебели</option>
        </select>

        <select name="payment" required>
            <option value="Наличные">Наличные</option>
            <option value="Банковская карта">Банковская карта</option>
        </select>

        <button type="submit">Создать заявку</button>
    </form>
</div>
</body>
</html>
