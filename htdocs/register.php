<?php
require_once 'includes/functions.php';
if (isLoggedIn()) redirect('index.php');
$pageTitle = 'Регистрация — ТОО «Колор»';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (!$name) $errors[] = 'Укажите имя';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Введите корректный email';
    if (strlen($password) < 6) $errors[] = 'Пароль должен быть не менее 6 символов';
    if ($password !== $confirm) $errors[] = 'Пароли не совпадают';

    if (empty($errors)) {
        if (register($name, $email, $phone, $password)) {
            flash('Регистрация прошла успешно! Добро пожаловать, ' . $name . '!');
            redirect('index.php');
        } else {
            $errors[] = 'Пользователь с таким email уже существует';
        }
    }
}
include 'includes/header.php';
?>

<div class="auth-page">
  <div class="auth-box">
    <div style="text-align:center;margin-bottom:32px;">
      <div class="logo" style="justify-content:center;color:var(--brown);">
        <span class="logo__icon" style="color:var(--gold);">◆</span>
        <span class="logo__text">КОЛОР <em style="color:var(--gold);">мебель</em></span>
      </div>
    </div>
    <h1>Регистрация</h1>
    <p>Создайте аккаунт для оформления заказов</p>

    <?php if($errors): ?>
      <div class="notice notice--warn">
        <?php foreach($errors as $e): ?><div>⚠️ <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Ваше имя *</label>
        <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Email *</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Телефон</label>
        <input type="tel" name="phone" placeholder="+7 (___) ___-__-__" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Пароль *</label>
        <input type="password" name="password" required>
      </div>
      <div class="form-group">
        <label>Подтвердите пароль *</label>
        <input type="password" name="confirm" required>
      </div>
      <button type="submit" class="btn btn--full btn--lg">Зарегистрироваться</button>
    </form>

    <p style="text-align:center;margin-top:20px;font-size:14px;color:var(--text-muted);">
      Уже есть аккаунт? <a href="login.php" style="color:var(--gold);font-weight:500;">Войти</a>
    </p>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
