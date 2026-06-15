<?php
require "db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = trim($_POST['password']);
    $name = trim($_POST['name']);
    $surname = trim($_POST['surname']);
    $middle_name = trim($_POST['middle_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);

    if (strlen($password) < 8) {
        $error = "Пароль должен содержать минимум 8 символов";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Некорректный email";
    } elseif (!preg_match('/^[0-9]{11}$/', $phone)) {

    $error = "Телефон должен содержать 11 цифр";
    } else {
    $check = $pdo->prepare("SELECT * FROM users WHERE login = ?");
    $check->execute([$login]);
    $user = $check->fetch();

if ($user) {
    $error = "Такой логин уже существует";
} else {
    $stmt = $pdo->prepare("INSERT INTO users (login, password, name, surname, middle_name, phone, email) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$login, $password, $name, $surname, $middle_name, $phone, $email]);

    header('Location: login.php');
    exit;
}
}
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Мой Не Сам</title>
</head>
<body>
<div class="login">
    <form method="POST">
        <h1>Регистрация</h1>
        <input type="text" name="login" placeholder="Логин" required>
        <input type="password" name="password" placeholder="Пароль" minlength="8" required>
        <input type="text" name="name" placeholder="Имя" required>
        <input type="text" name="surname" placeholder="Фамилия" required>
        <input type="text" name="middle_name" placeholder="Отчество" required>
        <input type="tel" name="phone" placeholder="7________" pattern="[0-9]{11}" required>
        <input type="email" name="email" placeholder="Адрес электронной почты" required>
        <a href="login.php">Уже есть аккаунт?</a>
        <button type="submit">Зарегистрироваться</button>
    </form>
    <?php if (isset($error)): ?>
    <p class="error"><?= $error ?></p>
<?php endif; ?>
</div>
</body>
</html>
