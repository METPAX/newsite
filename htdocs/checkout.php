<?php
require_once 'includes/functions.php';
$pageTitle = 'Оформление заказа — ТОО «Колор»';

if (!isLoggedIn()) redirect('login.php?redirect=checkout.php');
$items = getCartItems();
if (empty($items)) redirect('cart.php');

$total = array_sum(array_map(function($i) { return $i['price'] * $i['quantity']; }, $items));
$delivery = $total > 100000 ? 0 : 5000;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $comment = trim($_POST['comment'] ?? '');

    if (!$name) $errors[] = 'Укажите имя';
    if (!$phone) $errors[] = 'Укажите телефон';
    if (!$address) $errors[] = 'Укажите адрес доставки';

    if (empty($errors)) {
        $db = getDB();
        $stmtOrder = $db->prepare("INSERT INTO orders (user_id,total,name,phone,address,comment) VALUES (?,?,?,?,?,?) RETURNING id");
        $stmtOrder->execute([$_SESSION['user_id'], $total+$delivery, $name, $phone, $address, $comment]);
        $orderId = $stmtOrder->fetchColumn();

        $stmt = $db->prepare("INSERT INTO order_items (order_id,product_id,product_name,price,quantity) VALUES (?,?,?,?,?)");
        foreach ($items as $item) {
            $stmt->execute([$orderId, $item['product_id'], $item['name'], $item['price'], $item['quantity']]);
        }

        $db->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$_SESSION['user_id']]);
        flash("Заказ №{$orderId} успешно оформлен! Мы свяжемся с вами для подтверждения.");
        redirect('profile.php');
    }
}

include 'includes/header.php';
?>

<div class="page-hero">
  <h1>Оформление заказа</h1>
  <p>Укажите данные для доставки</p>
</div>

<div style="display:grid;grid-template-columns:1fr 380px;gap:40px;max-width:1100px;margin:0 auto;padding:60px 48px;">
  <!-- FORM -->
  <div>
    <?php if($errors): ?>
      <div class="notice notice--warn">
        <?php foreach($errors as $e): ?><div>⚠️ <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div style="background:var(--white);border:1px solid var(--border);padding:36px;border-radius:var(--radius);">
      <h2 style="font-family:var(--font-display);font-size:28px;margin-bottom:28px;">Данные получателя</h2>
      <form method="POST">
        <div class="form-group">
          <label>Имя и фамилия *</label>
          <input type="text" name="name" value="<?= htmlspecialchars($_SESSION['name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label>Телефон *</label>
          <input type="tel" name="phone" placeholder="+7 (___) ___-__-__" required>
        </div>
        <div class="form-group">
          <label>Адрес доставки *</label>
          <input type="text" name="address" placeholder="Город, улица, дом, квартира" required>
        </div>
        <div class="form-group">
          <label>Комментарий к заказу</label>
          <textarea name="comment" placeholder="Дополнительные пожелания, удобное время доставки..."></textarea>
        </div>
        <div class="notice notice--info">
          💳 Оплата при получении. Менеджер свяжется для подтверждения заказа.
        </div>
        <button type="submit" class="btn btn--full btn--lg" style="margin-top:20px;">Подтвердить заказ</button>
      </form>
    </div>
  </div>

  <!-- ORDER SUMMARY -->
  <div>
    <div class="cart-summary">
      <h3>Ваш заказ</h3>
      <?php foreach ($items as $item): ?>
      <div class="summary-row">
        <span><?= htmlspecialchars($item['name']) ?> ×<?= $item['quantity'] ?></span>
        <span><?= formatPrice($item['price'] * $item['quantity']) ?></span>
      </div>
      <?php endforeach; ?>
      <div class="summary-row" style="margin-top:8px;">
        <span>Доставка</span>
        <span><?= $delivery == 0 ? 'Бесплатно' : formatPrice($delivery) ?></span>
      </div>
      <div class="summary-total">
        <span>Итого</span>
        <span><?= formatPrice($total + $delivery) ?></span>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
