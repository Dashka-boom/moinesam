//registr.php
<?php
require "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $check = $pdo->prepare("SELECT id FROM users WHERE login = ?");
        $check->execute([$login]);
        $user = $check->fetch();

        if ($user) {
            $error = "Такой логин уже существует";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (login, password, name, surname, middle_name, phone, email) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$login, $hash, $name, $surname, $middle_name, $phone, $email]);

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
    <title>Регистрация</title>
</head>
<body>
<div class="page auth-page">
    <form method="POST" class="auth-card">
        <h1 class="title">Регистрация</h1>
        <input class="field" type="text" name="login" placeholder="Логин" required>
        <input class="field" type="password" name="password" placeholder="Пароль" minlength="8" required>
        <input class="field" type="text" name="name" placeholder="Имя" required>
        <input class="field" type="text" name="surname" placeholder="Фамилия" required>
        <input class="field" type="text" name="middle_name" placeholder="Отчество" required>
        <input class="field" type="tel" name="phone" placeholder="79991234567" pattern="[0-9]{11}" required>
        <input class="field" type="email" name="email" placeholder="Адрес электронной почты" required>
        <a class="text-link" href="login.php">Уже есть аккаунт?</a>
        <button class="btn" type="submit">Зарегистрироваться</button>
    </form>

    <?php if (isset($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
</div>
</body>
</html>

//login.php
<?php
session_start();
require "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login']);
    $password = trim($_POST['password']);

    if ($login === 'adminka' && $password === 'password') {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        header('Location: admin.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['surname'] . ' ' . $user['name'] . ' ' . $user['middle_name'];
        header('Location: request.php');
        exit;
    } else {
        $error = 'Неверный логин или пароль';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Авторизация</title>
</head>
<body>
<div class="page auth-page">
    <form method="POST" class="auth-card">
        <h1 class="title">Авторизация</h1>
        <input class="field" type="text" name="login" placeholder="Логин" required>
        <input class="field" type="password" name="password" placeholder="Пароль" required>
        <a class="text-link" href="registr.php">Зарегистрироваться</a>
        <button class="btn" type="submit">Войти</button>

        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </form>
</div>
</body>
</html>

//admin.php
<?php
session_start();
require "db.php";

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $status = trim($_POST['status']);
    $cancel_reason = trim($_POST['cancel_reason']);

    $allowed = ['Новая заявка', 'В работе', 'Выполнено', 'Отменено'];

    if (!in_array($status, $allowed, true)) {
        $error = "Некорректный статус";
    } elseif ($status === 'Отменено' && $cancel_reason === '') {
        $error = "Укажите причину отмены";
    } else {
        if ($status !== 'Отменено') {
            $cancel_reason = '';
        }

        $stmt = $pdo->prepare("UPDATE request SET status = ?, cancel_reason = ? WHERE id = ?");
        $stmt->execute([$status, $cancel_reason, $id]);

        header('Location: admin.php');
        exit;
    }
}

$stmt = $pdo->query("SELECT request.*, users.name, users.surname, users.middle_name, users.phone, users.email FROM request JOIN users ON request.user_id = users.id ORDER BY request.id DESC");
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <div class="topbar">
        <h1 class="title">Панель администратора</h1>
        <a class="text-link" href="logout.php">Выйти</a>
    </div>

    <?php if (isset($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php foreach ($requests as $request): ?>
        <div class="card">
            <p><b>ФИО:</b> <?= htmlspecialchars($request['surname'] . ' ' . $request['name'] . ' ' . $request['middle_name']) ?></p>
            <p><b>Телефон:</b> <?= htmlspecialchars($request['phone']) ?></p>
            <p><b>Email:</b> <?= htmlspecialchars($request['email']) ?></p>
            <p><b>Адрес:</b> <?= htmlspecialchars($request['address']) ?></p>
            <p><b>Контактные данные:</b> <?= htmlspecialchars($request['contacts']) ?></p>
            <p><b>Дата и время:</b> <?= htmlspecialchars($request['date_time']) ?></p>
            <p><b>Услуга:</b> <?= htmlspecialchars($request['service']) ?></p>
            <p><b>Оплата:</b> <?= htmlspecialchars($request['payment']) ?></p>
            <p><b>Статус:</b> <?= htmlspecialchars($request['status']) ?></p>

            <?php if (!empty($request['cancel_reason'])): ?>
                <p><b>Причина отмены:</b> <?= htmlspecialchars($request['cancel_reason']) ?></p>
            <?php endif; ?>

            <form method="POST" class="admin-form">
                <input type="hidden" name="id" value="<?= $request['id'] ?>">

                <select class="field" name="status" required>
                    <option value="">Выберите статус</option>
                    <option value="Новая заявка">Новая заявка</option>
                    <option value="В работе">В работе</option>
                    <option value="Выполнено">Выполнено</option>
                    <option value="Отменено">Отменено</option>
                </select>

                <input class="field" type="text" name="cancel_reason" placeholder="Причина отмены">

                <button class="btn" type="submit">Сохранить</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>

//request.php
<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM request WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <div class="topbar">
        <h1 class="title">Мои заявки</h1>
        <a class="text-link" href="logout.php">Выйти</a>
    </div>

    <div class="links">
        <a class="btn-link" href="create-request.php">Создать заявку</a>
    </div>

    <?php if ($requests): ?>
        <?php foreach ($requests as $request): ?>
            <div class="card">
                <p><b>Адрес:</b> <?= htmlspecialchars($request['address']) ?></p>
                <p><b>Контактные данные:</b> <?= htmlspecialchars($request['contacts']) ?></p>
                <p><b>Дата и время:</b> <?= htmlspecialchars($request['date_time']) ?></p>
                <p><b>Вид услуги:</b> <?= htmlspecialchars($request['service']) ?></p>
                <p><b>Тип оплаты:</b> <?= htmlspecialchars($request['payment']) ?></p>
                <p><b>Статус:</b> <?= htmlspecialchars($request['status']) ?></p>
                <?php if (!empty($request['cancel_reason'])): ?>
                    <p><b>Причина отмены:</b> <?= htmlspecialchars($request['cancel_reason']) ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="empty">Заявок пока нет.</p>
    <?php endif; ?>
</div>
</body>
</html>

//create-request.php
<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address']);
    $contacts = trim($_POST['contacts']);
    $date_time = trim($_POST['date_time']);
    $service = trim($_POST['service']);
    $payment = trim($_POST['payment']);

    if ($address && $contacts && $date_time && $service && $payment) {
        $stmt = $pdo->prepare("INSERT INTO request (user_id, address, contacts, date_time, service, payment, status, cancel_reason) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $address, $contacts, $date_time, $service, $payment, 'Новая заявка', '']);
        header('Location: request.php');
        exit;
    } else {
        $error = "Заполните все поля";
    }
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
<div class="page auth-page">
    <form method="POST" class="auth-card">
        <h1 class="title">Создание заявки</h1>
        <input class="field" type="text" name="address" placeholder="Адрес" required>
        <input class="field" type="text" name="contacts" placeholder="Контактные данные" required>
        <input class="field" type="datetime-local" name="date_time" required>

        <select class="field" name="service" required>
            <option value="">Выберите вид услуги</option>
            <option value="Общий клининг">Общий клининг</option>
            <option value="Генеральная уборка">Генеральная уборка</option>
            <option value="Послестроительная уборка">Послестроительная уборка</option>
            <option value="Химчистка ковров и мебели">Химчистка ковров и мебели</option>
        </select>

        <select class="field" name="payment" required>
            <option value="">Выберите тип оплаты</option>
            <option value="Наличные">Наличные</option>
            <option value="Банковская карта">Банковская карта</option>
        </select>

        <button class="btn" type="submit">Создать заявку</button>

        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </form>
</div>
</body>
</html>

//db.php
<?php
$host = "localhost";
$dbname = "moinesam";
$user = "root";
$password = "";

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
?>

//logout.php
<?php
session_start();
session_destroy();
header('Location: login.php');
exit;
?>

//style.css
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background: #f4f6f8;
    color: #222;
    min-height: 100vh;
    padding: 16px;
}

.page {
    width: 100%;
    max-width: 920px;
    margin: 0 auto;
}

.auth-page {
    max-width: 390px;
}

.auth-card,
.login {
    background: #ffffff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.08);
}

.title {
    text-align: center;
    margin-bottom: 18px;
    color: #262243;
    font-size: 24px;
}

form {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.field,
input,
select {
    width: 100%;
    border-radius: 12px;
    border: 1px solid #ccc;
    padding: 12px;
    font-size: 14px;
    background: #fff;
}

.btn,
button {
    background: #262243;
    color: #ffffff;
    border-radius: 12px;
    padding: 12px;
    cursor: pointer;
    border: none;
    font-size: 15px;
}

.btn:hover,
button:hover {
    background: #3a3560;
}

.text-link {
    color: #262243;
    text-decoration: none;
    display: inline-block;
    margin-top: 6px;
}

.btn-link {
    display: inline-block;
    background: #262243;
    color: #fff;
    text-decoration: none;
    padding: 12px 16px;
    border-radius: 12px;
}

.card {
    background: #ffffff;
    padding: 18px;
    border-radius: 14px;
    margin-top: 18px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.06);
    border-left: 5px solid #262243;
}

.card p {
    margin-bottom: 8px;
    line-height: 1.35;
}

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}

.links {
    margin: 16px 0 0;
}

.error {
    color: red;
    margin-top: 12px;
    text-align: center;
}

.empty {
    margin-top: 20px;
    text-align: center;
    color: #666;
}

.admin-form {
    margin-top: 14px;
}

@media (max-width: 390px) {
    body {
        padding: 12px;
    }

    .auth-card,
    .login,
    .card {
        padding: 16px;
    }

    .title {
        font-size: 22px;
    }

    .topbar {
        flex-direction: column;
        align-items: flex-start;
    }

    .btn-link,
    .text-link,
    .btn,
    button,
    .field,
    input,
    select {
        width: 100%;
    }

    .text-link {
        text-align: center;
    }
}