<?php
require_once '../includes/functions.php';
if (!isAdmin()) { flash('Доступ запрещён', 'error'); redirect('../login.php'); }
$db = getDB();
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("
    SELECT o.*, p.name as uname, u.email
    FROM orders o
    LEFT JOIN public.profiles p ON o.user_id = p.id
    LEFT JOIN auth.users u ON p.id = u.id
    WHERE o.id = ?
");
$stmt->execute([$id]);
$order = $stmt->fetch();
if (!$order) { flash('Заказ не найден', 'error'); redirect('orders.php'); }

$items = $db->prepare("SELECT oi.*, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id=p.id WHERE oi.order_id=?");
$items->execute([$id]);
$items = $items->fetchAll();

$pageTitle = "Заказ #{$id} — Администрирование";
$statusMap = ['new' => 'Новый', 'processing' => 'В обработке', 'done' => 'Выполнен'];
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
    <div style="display:flex;align-items:center;gap:16px;margin-bottom:32px;">
      <a href="orders.php" style="color:var(--text-muted);font-size:14px;">← Назад к заказам</a>
      <h1 style="margin:0;">Заказ #<?= $order['id'] ?></h1>
      <span class="status-badge status-<?= $order['status'] ?>"><?= $statusMap[$order['status']] ?></span>
    </div>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:28px;">
      <!-- ORDER ITEMS -->
      <div>
        <div style="background:var(--white);border:1px solid var(--border);border-radius:2px;overflow:hidden;margin-bottom:20px;">
          <div style="padding:20px 24px;border-bottom:1px solid var(--border);font-family:var(--font-display);font-size:20px;">Состав заказа</div>
          <table class="admin-table" style="border:none;">
            <thead><tr><th>Товар</th><th>Цена</th><th>Кол-во</th><th>Сумма</th></tr></thead>
            <tbody>
              <?php foreach($items as $item): ?>
              <tr>
                <td><strong><?= htmlspecialchars($item['product_name']) ?></strong></td>
                <td><?= formatPrice($item['price']) ?></td>
                <td><?= $item['quantity'] ?> шт.</td>
                <td><strong><?= formatPrice($item['price'] * $item['quantity']) ?></strong></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <div style="padding:16px 24px;border-top:2px solid var(--border);display:flex;justify-content:flex-end;gap:32px;">
            <span style="font-family:var(--font-display);font-size:22px;">Итого: <strong><?= formatPrice($order['total']) ?></strong></span>
          </div>
        </div>

        <?php if($order['comment']): ?>
        <div style="background:var(--white);border:1px solid var(--border);border-radius:2px;padding:20px 24px;">
          <div style="font-size:12px;letter-spacing:1.5px;text-transform:uppercase;color:var(--text-muted);margin-bottom:8px;">Комментарий клиента</div>
          <p><?= htmlspecialchars($order['comment']) ?></p>
        </div>
        <?php endif; ?>
      </div>

      <!-- ORDER INFO -->
      <div>
        <div style="background:var(--white);border:1px solid var(--border);border-radius:2px;padding:24px;margin-bottom:16px;">
          <div style="font-family:var(--font-display);font-size:20px;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border);">Клиент</div>
          <p style="font-size:14px;margin-bottom:6px;"><strong><?= htmlspecialchars($order['uname'] ?? $order['name']) ?></strong></p>
          <p style="font-size:13px;color:var(--text-muted);margin-bottom:4px;">📧 <?= htmlspecialchars($order['email'] ?? '—') ?></p>
          <p style="font-size:13px;color:var(--text-muted);margin-bottom:4px;">📞 <?= htmlspecialchars($order['phone']) ?></p>
          <p style="font-size:13px;color:var(--text-muted);">📍 <?= htmlspecialchars($order['address']) ?></p>
        </div>

        <div style="background:var(--white);border:1px solid var(--border);border-radius:2px;padding:24px;">
          <div style="font-family:var(--font-display);font-size:20px;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border);">Статус заказа</div>
          <p style="font-size:12px;color:var(--text-muted);margin-bottom:4px;">Дата: <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
          <form method="POST" action="order-status.php" style="margin-top:16px;">
            <input type="hidden" name="id" value="<?= $order['id'] ?>">
            <input type="hidden" name="redirect" value="order.php?id=<?= $order['id'] ?>">
            <div class="form-group">
              <label>Изменить статус</label>
              <select name="status">
                <?php foreach($statusMap as $k => $v): ?>
                  <option value="<?= $k ?>" <?= $order['status']==$k?'selected':'' ?>><?= $v ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="submit" class="btn btn--full">Сохранить</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
