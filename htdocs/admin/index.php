<?php
require_once '../includes/functions.php';
if (!isAdmin()) { flash('Доступ запрещён', 'error'); redirect('../login.php'); }
$pageTitle = 'Администрирование — ТОО «Колор»';
$db = getDB();
$stats = [
    'Заказов' => $db->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'Товаров' => $db->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'Клиентов' => $db->query("SELECT COUNT(*) FROM public.profiles WHERE role='customer'")->fetchColumn(),
    'Выручка' => formatPrice($db->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status='done'")->fetchColumn()),
];
$orders = $db->query("
    SELECT o.*, p.name as uname, u.email
    FROM orders o
    LEFT JOIN public.profiles p ON o.user_id = p.id
    LEFT JOIN auth.users u ON p.id = u.id
    ORDER BY o.created_at DESC
    LIMIT 20
")->fetchAll();
$statusMap = ['new'=>'Новый','processing'=>'В обработке','done'=>'Выполнен'];
include '../includes/header.php';
?>

<div class="admin-layout">
  <nav class="admin-sidebar">
    <div style="padding:0 24px 24px;color:rgba(245,240,232,.4);font-size:11px;letter-spacing:2px;text-transform:uppercase;">Панель управления</div>
    <a href="index.php" class="active">📊 Дашборд</a>
    <a href="products.php">📦 Товары</a>
    <a href="orders.php">🧾 Заказы</a>
    <a href="users.php">👥 Клиенты</a>
    <a href="settings.php">⚙️ Настройки</a>
    <a href="../index.php">← На сайт</a>
  </nav>

  <div class="admin-content">
    <h1>Дашборд</h1>
    <div class="admin-grid">
      <?php foreach ($stats as $label => $val): ?>
      <div class="admin-stat">
        <div class="admin-stat__num"><?= $val ?></div>
        <div class="admin-stat__label"><?= $label ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <h2 style="font-family:var(--font-display);font-size:26px;margin-bottom:16px;">Последние заказы</h2>
    <table class="admin-table">
      <thead>
        <tr><th>№</th><th>Клиент</th><th>Телефон</th><th>Сумма</th><th>Статус</th><th>Дата</th><th>Действие</th></tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
        <tr>
          <td><strong>#<?= $o['id'] ?></strong></td>
          <td><?= htmlspecialchars($o['uname'] ?? $o['name']) ?></td>
          <td><?= htmlspecialchars($o['phone']) ?></td>
          <td><strong><?= formatPrice($o['total']) ?></strong></td>
          <td>
            <form method="POST" action="order-status.php" style="display:inline;">
              <input type="hidden" name="id" value="<?= $o['id'] ?>">
              <select name="status" onchange="this.form.submit()" style="padding:4px 8px;border:1px solid var(--border);border-radius:2px;font-family:var(--font-body);font-size:12px;">
                <?php foreach($statusMap as $k=>$v): ?>
                  <option value="<?= $k ?>" <?= $o['status']==$k?'selected':'' ?>><?= $v ?></option>
                <?php endforeach; ?>
              </select>
            </form>
          </td>
          <td style="font-size:12px;color:var(--text-muted);"><?= date('d.m.Y H:i',strtotime($o['created_at'])) ?></td>
          <td><a href="order.php?id=<?= $o['id'] ?>" style="color:var(--gold);font-size:13px;">Открыть →</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
