<?php
require_once 'includes/functions.php';
if (isLoggedIn()) redirect('index.php');
$pageTitle = 'Вход — ТОО «Колор»';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (login($email, $password)) {
        flash('Добро пожаловать, ' . $_SESSION['name'] . '!');
        redirect($_GET['redirect'] ?? 'index.php');
    } else {
        $error = 'Неверный email или пароль';
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
    <h1>Вход в аккаунт</h1>
    <p>Войдите, чтобы оформлять заказы и управлять покупками</p>

    <?php if($error): ?>
      <div class="notice notice--warn"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required autofocus value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Пароль</label>
        <input type="password" name="password" required>
      </div>
      <button type="submit" class="btn btn--full btn--lg" style="margin-top:8px;">Войти</button>
    </form>

    <p style="text-align:center;margin-top:20px;font-size:14px;color:var(--text-muted);">
      Нет аккаунта? <a href="register.php" style="color:var(--gold);font-weight:500;">Зарегистрироваться</a>
    </p>
    <?php /* Данные для входа в панель администратора хранятся в includes/db.php (функция seedData) */ ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
