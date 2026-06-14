<?php
require_once '../includes/functions.php';
if (!isAdmin()) { flash('Доступ запрещён', 'error'); redirect('../login.php'); }
$pageTitle = 'Заказы — Администрирование';
$db = getDB();

$orders = $db->query("
    SELECT o.*, p.name as uname, u.email
    FROM orders o
    LEFT JOIN public.profiles p ON o.user_id = p.id
    LEFT JOIN auth.users u ON p.id = u.id
    ORDER BY o.created_at DESC
")->fetchAll();

$statusMap = ['new' => 'Новый', 'processing' => 'В обработке', 'done' => 'Выполнен'];
$statusClass = ['new' => 'status-new', 'processing' => 'status-processing', 'done' => 'status-done'];

include '../includes/header.php';
?>

<div class="admin-layout">
  <nav class="admin-sidebar">
    <div style="padding:0 24px 24px;color:rgba(245,240,232,.4);font-size:11px;letter-spacing:2px;text-transform:uppercase;">Панель управления</div>
    <a href="index.php">📊 Дашборд</a>
    <a href="products.php">📦 Товары</a>
    <a href="orders.php" class="active">🧾 Заказы</a>
    <a href="users.php">👥 Клиенты</a>
    <a href="settings.php">⚙️ Настройки</a>
    <a href="../index.php">← На сайт</a>
  </nav>

  <div class="admin-content">
    <h1>Заказы (<?= count($orders) ?>)</h1>

    <?php if (empty($orders)): ?>
      <div class="empty-state"><div class="empty-state__icon">📦</div><h3>Заказов пока нет</h3></div>
    <?php else: ?>
    <table class="admin-table">
      <thead>
        <tr>
          <th>№</th><th>Клиент</th><th>Телефон</th><th>Адрес</th><th>Сумма</th><th>Статус</th><th>Дата</th><th>Детали</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
        <tr>
          <td><strong>#<?= $o['id'] ?></strong></td>
          <td>
            <strong><?= htmlspecialchars($o['uname'] ?? $o['name']) ?></strong><br>
            <span style="font-size:12px;color:var(--text-muted);"><?= htmlspecialchars($o['email'] ?? '') ?></span>
          </td>
          <td><?= htmlspecialchars($o['phone']) ?></td>
          <td style="font-size:12px;color:var(--text-muted);max-width:150px;"><?= htmlspecialchars($o['address']) ?></td>
          <td><strong><?= formatPrice($o['total']) ?></strong></td>
          <td>
            <form method="POST" action="order-status.php">
              <input type="hidden" name="id" value="<?= $o['id'] ?>">
              <select name="status" onchange="this.form.submit()" style="padding:5px 8px;border:1px solid var(--border);border-radius:2px;font-family:var(--font-body);font-size:12px;background:var(--cream);">
                <?php foreach($statusMap as $k => $v): ?>
                  <option value="<?= $k ?>" <?= $o['status'] == $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
              </select>
            </form>
          </td>
          <td style="font-size:12px;color:var(--text-muted);"><?= date('d.m.Y<br>H:i', strtotime($o['created_at'])) ?></td>
          <td><a href="order.php?id=<?= $o['id'] ?>" class="btn btn--sm">Открыть</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
