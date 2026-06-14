<?php
require_once 'includes/functions.php';
if (!isLoggedIn()) redirect('login.php');
$pageTitle = 'Профиль — ТОО «Колор»';

$db = getDB();
$stmt = $db->prepare("
    SELECT p.id, p.name, p.phone, p.role, u.email
    FROM public.profiles p
    JOIN auth.users u ON p.id = u.id
    WHERE p.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$orders = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$orders->execute([$_SESSION['user_id']]);
$orders = $orders->fetchAll();

$errors = [];
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $newpass = $_POST['new_password'] ?? '';
    
    if (!$name) $errors[] = 'Укажите имя';
    if (empty($errors)) {
        if ($newpass && strlen($newpass) < 6) {
            $errors[] = 'Пароль минимум 6 символов';
        }
        
        if (empty($errors)) {
            // Обновляем публичные данные профиля
            $db->prepare("UPDATE public.profiles SET name = ?, phone = ? WHERE id = ?")
               ->execute([$name, $phone, $_SESSION['user_id']]);
            
            // Если введен новый пароль, обновляем его во встроенной таблице auth.users
            if ($newpass) {
                $hashedPassword = password_hash($newpass, PASSWORD_BCRYPT);
                $db->prepare("UPDATE auth.users SET encrypted_password = ? WHERE id = ?")
                   ->execute([$hashedPassword, $_SESSION['user_id']]);
            }
            
            $_SESSION['name'] = $name;
            flash('Профиль обновлён!');
            redirect('profile.php');
        }
    }
}

$statusMap = ['new'=>'Новый','processing'=>'В обработке','done'=>'Выполнен'];
$statusClass = ['new'=>'status-new','processing'=>'status-processing','done'=>'status-done'];
include 'includes/header.php';
?>

<div class="profile-page">
  <h1 style="font-family:var(--font-display);font-size:40px;margin-bottom:8px;">Личный кабинет</h1>
  <p style="color:var(--text-muted);margin-bottom:32px;">Добро пожаловать, <strong><?= htmlspecialchars($_SESSION['name']) ?></strong></p>

  <div class="profile-tabs">
    <button class="tab-btn active" data-tab="tab-orders">Мои заказы (<?= count($orders) ?>)</button>
    <button class="tab-btn" data-tab="tab-profile">Настройки профиля</button>
  </div>

  <!-- ORDERS TAB -->
  <div id="tab-orders" class="tab-panel active">
    <?php if(empty($orders)): ?>
      <div class="empty-state">
        <div class="empty-state__icon">📦</div>
        <h3>Заказов пока нет</h3>
        <p>Перейдите в каталог и сделайте первый заказ</p>
        <a href="catalog.php" class="btn">В каталог</a>
      </div>
    <?php else: ?>
      <table class="orders-table">
        <thead>
          <tr>
            <th>№</th><th>Дата</th><th>Сумма</th><th>Статус</th><th>Адрес</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
          <tr>
            <td><strong>#<?= $o['id'] ?></strong></td>
            <td><?= date('d.m.Y H:i', strtotime($o['created_at'])) ?></td>
            <td><strong><?= formatPrice($o['total']) ?></strong></td>
            <td><span class="status-badge <?= $statusClass[$o['status']] ?? '' ?>"><?= $statusMap[$o['status']] ?? $o['status'] ?></span></td>
            <td style="color:var(--text-muted);font-size:13px;"><?= htmlspecialchars($o['address']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <!-- PROFILE TAB -->
  <div id="tab-profile" class="tab-panel">
    <div style="max-width:500px;">
      <?php if($errors): ?>
        <div class="notice notice--warn">
          <?php foreach($errors as $e): ?><div>⚠️ <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
        </div>
      <?php endif; ?>
      <form method="POST">
        <input type="hidden" name="update_profile" value="1">
        <div class="form-group">
          <label>Имя</label>
          <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled style="opacity:.6;">
        </div>
        <div class="form-group">
          <label>Телефон</label>
          <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Новый пароль <span style="color:var(--text-muted);font-weight:300;">(оставьте пустым, если не меняете)</span></label>
          <input type="password" name="new_password">
        </div>
        <button type="submit" class="btn">Сохранить изменения</button>
      </form>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
