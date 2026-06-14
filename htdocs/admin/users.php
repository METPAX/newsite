<?php
require_once '../includes/functions.php';
if (!isAdmin()) { flash('Доступ запрещён', 'error'); redirect('../login.php'); }
$pageTitle = 'Клиенты — Администрирование';
$db = getDB();

$users = $db->query("
    SELECT 
        p.id, p.name, p.phone, p.role, p.created_at, u.email,
        COUNT(o.id) as orders_count, COALESCE(SUM(o.total),0) as total_spent
    FROM public.profiles p
    JOIN auth.users u ON p.id = u.id
    LEFT JOIN public.orders o ON p.id = o.user_id
    GROUP BY p.id, u.id
    ORDER BY p.created_at DESC
")->fetchAll();

include '../includes/header.php';
?>

<div class="admin-layout">
  <nav class="admin-sidebar">
    <div style="padding:0 24px 24px;color:rgba(245,240,232,.4);font-size:11px;letter-spacing:2px;text-transform:uppercase;">Панель управления</div>
    <a href="index.php">📊 Дашборд</a>
    <a href="products.php">📦 Товары</a>
    <a href="orders.php">🧾 Заказы</a>
    <a href="users.php" class="active">👥 Клиенты</a>
    <a href="settings.php">⚙️ Настройки</a>
    <a href="../index.php">← На сайт</a>
  </nav>

  <div class="admin-content">
    <h1>Клиенты (<?= count($users) ?>)</h1>

    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Имя</th>
          <th>Email</th>
          <th>Телефон</th>
          <th>Роль</th>
          <th>Заказов</th>
          <th>Потрачено</th>
          <th>Дата регистрации</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td style="color:var(--text-muted);">#<?= $u['id'] ?></td>
          <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
          <td style="font-size:13px;"><?= htmlspecialchars($u['email']) ?></td>
          <td style="font-size:13px;"><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
          <td>
            <?php
            $roleNames = [
                'admin'     => 'Администратор',
                'designer'  => 'Дизайнер',
                'installer' => 'Мебельщик',
                'secretary' => 'Секретарь',
                'customer'  => 'Клиент'
            ];
            $roleClasses = [
                'admin'     => 'status-processing',
                'designer'  => 'status-done',
                'installer' => 'status-done',
                'secretary' => 'status-done',
                'customer'  => 'status-new'
            ];
            $rName = $roleNames[$u['role']] ?? $u['role'];
            $rClass = $roleClasses[$u['role']] ?? 'status-new';
            ?>
            <span class="status-badge <?= $rClass ?>">
              <?= htmlspecialchars($rName) ?>
            </span>
          </td>
          <td style="text-align:center;"><?= $u['orders_count'] ?></td>
          <td><strong><?= $u['total_spent'] > 0 ? formatPrice($u['total_spent']) : '—' ?></strong></td>
          <td style="font-size:12px;color:var(--text-muted);"><?= date('d.m.Y', strtotime($u['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
