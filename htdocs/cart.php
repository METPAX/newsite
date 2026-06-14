<?php
require_once 'includes/functions.php';
$pageTitle = 'Корзина — ТОО «Колор»';

if (!isLoggedIn()) {
    flash('Войдите, чтобы просмотреть корзину', 'error');
    redirect('login.php?redirect=cart.php');
}

$items = getCartItems();
$total = array_sum(array_map(function($i) { return $i['price'] * $i['quantity']; }, $items));
$delivery = $total > 100000 ? 0 : 5000;

include 'includes/header.php';
?>

<div class="page-hero">
  <h1>Моя корзина</h1>
  <?php if($items): ?>
    <p><?= count($items) ?> <?= count($items) == 1 ? 'товар' : (count($items) < 5 ? 'товара' : 'товаров') ?> на сумму <?= formatPrice($total) ?></p>
  <?php endif; ?>
</div>

<?php if (empty($items)): ?>
  <div class="empty-state" style="padding:100px 24px;">
    <div class="empty-state__icon">🛒</div>
    <h3>Корзина пуста</h3>
    <p>Добавьте товары из каталога, чтобы оформить заказ</p>
    <a href="catalog.php" class="btn">Перейти в каталог</a>
  </div>
<?php else: ?>
<div class="cart-page">
  <!-- ITEMS -->
  <div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
      <h2 style="font-family:var(--font-display);font-size:28px;">Товары в корзине</h2>
      <a href="cart-action.php?action=clear" class="btn btn--ghost btn--sm btn--dark" 
         onclick="return confirm('Очистить корзину?')">Очистить всё</a>
    </div>
    <div class="cart-items">
      <?php foreach ($items as $item): ?>
      <div class="cart-item">
        <div class="cart-item__img">
          <?php
            $imgPath = 'assets/img/products/' . ($item['image'] ?? '');
            if (file_exists(__DIR__ . '/' . $imgPath)):
          ?>
            <img src="<?= $imgPath ?>" alt="">
          <?php else: ?>
            🛋️
          <?php endif; ?>
        </div>
        <div>
          <a href="product.php?id=<?= $item['product_id'] ?>">
            <div class="cart-item__name"><?= htmlspecialchars($item['name']) ?></div>
          </a>
          <div class="cart-item__price"><?= formatPrice($item['price']) ?> за шт.</div>
        </div>
        <div class="cart-item__qty">
          <form method="POST" action="cart-action.php" style="display:flex;align-items:center;gap:8px;">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
            <button type="button" class="qty-btn" onclick="this.nextElementSibling.value=Math.max(1,+this.nextElementSibling.value-1);this.form.submit()">−</button>
            <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="99" class="qty-input" onchange="this.form.submit()">
            <button type="button" class="qty-btn" onclick="this.previousElementSibling.value=+this.previousElementSibling.value+1;this.form.submit()">+</button>
          </form>
        </div>
        <div class="cart-item__total"><?= formatPrice($item['price'] * $item['quantity']) ?></div>
        <a href="cart-action.php?action=remove&cart_id=<?= $item['id'] ?>" class="cart-item__del" title="Удалить">✕</a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- SUMMARY -->
  <div class="cart-summary">
    <h3>Итого</h3>
    <div class="summary-row">
      <span>Товары (<?= count($items) ?> шт.)</span>
      <span><?= formatPrice($total) ?></span>
    </div>
    <div class="summary-row">
      <span>Доставка</span>
      <span><?= $delivery == 0 ? 'Бесплатно' : formatPrice($delivery) ?></span>
    </div>
    <?php if($delivery > 0): ?>
    <div class="notice notice--info" style="margin-top:12px;font-size:12px;">
      До бесплатной доставки осталось <?= formatPrice(100000 - $total) ?>
    </div>
    <?php endif; ?>
    <div class="summary-total">
      <span>К оплате</span>
      <span><?= formatPrice($total + $delivery) ?></span>
    </div>
    <a href="checkout.php" class="btn btn--full btn--lg" style="margin-top:16px;">Оформить заказ</a>
    <a href="catalog.php" class="btn btn--full btn--ghost btn--dark" style="margin-top:8px;">← Продолжить покупки</a>
  </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
