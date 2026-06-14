<?php
require_once 'includes/functions.php';
$id = (int)($_GET['id'] ?? 0);
$p = getProduct($id);
if (!$p) { header('Location: catalog.php'); exit; }
$pageTitle = $p['name'] . ' — ТОО «Колор»';
include 'includes/header.php';

$icons = ['sofa'=>'🛋️','bed'=>'🛏️','kitchen'=>'🍽️','wardrobe'=>'🗄️','table'=>'🪑','chair'=>'🪑','shelf'=>'📚','armchair'=>'💺','kids'=>'🧸'];
$icon = '🛋️';
foreach($icons as $k=>$v) if(strpos($p['image'],$k)!==false){$icon=$v;break;}
?>

<div class="product-page">
  <!-- IMAGE -->
  <div class="product-image">
    <?php $imgPath = 'assets/img/products/' . $p['image'];
    if (file_exists(__DIR__ . '/' . $imgPath)): ?>
      <img src="<?= $imgPath ?>" alt="<?= htmlspecialchars($p['name']) ?>">
    <?php else: ?>
      <span style="font-size:160px;"><?= $icon ?></span>
    <?php endif; ?>
  </div>

  <!-- INFO -->
  <div class="product-info">
    <div class="product-info__breadcrumb">
      <a href="index.php">Главная</a> / 
      <a href="catalog.php">Каталог</a> / 
      <a href="catalog.php?category=<?= $p['cat_slug'] ?>"><?= htmlspecialchars($p['cat_name']) ?></a> / 
      <?= htmlspecialchars($p['name']) ?>
    </div>

    <h1><?= htmlspecialchars($p['name']) ?></h1>
    <div class="product-info__cat"><?= htmlspecialchars($p['cat_name']) ?></div>

    <div class="product-info__price">
      <div class="price">
        <span class="price__current"><?= formatPrice($p['price']) ?></span>
        <?php if($p['old_price']): ?>
          <span class="price__old"><?= formatPrice($p['old_price']) ?></span>
          <span style="background:var(--gold);color:var(--brown);font-size:11px;font-weight:700;padding:3px 8px;">
            −<?= round((1-$p['price']/$p['old_price'])*100) ?>%
          </span>
        <?php endif; ?>
      </div>
    </div>

    <dl class="product-meta">
      <?php if($p['material']): ?>
        <dt>Материал</dt><dd><?= htmlspecialchars($p['material']) ?></dd>
      <?php endif; ?>
      <?php if($p['dimensions']): ?>
        <dt>Размеры</dt><dd><?= htmlspecialchars($p['dimensions']) ?></dd>
      <?php endif; ?>
      <?php if($p['color']): ?>
        <dt>Цвет</dt><dd><?= htmlspecialchars($p['color']) ?></dd>
      <?php endif; ?>
      <dt>Наличие</dt>
      <dd><?= $p['in_stock'] ? '✅ В наличии' : '❌ Нет в наличии' ?></dd>
    </dl>

    <?php if($p['description']): ?>
      <p class="product-info__desc"><?= nl2br(htmlspecialchars($p['description'])) ?></p>
    <?php endif; ?>

    <?php if($p['in_stock']): ?>
      <form method="POST" action="cart-action.php">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
        <input type="hidden" name="redirect" value="product.php?id=<?= $p['id'] ?>">
        <div class="qty-control">
          <button type="button" class="qty-btn" id="qty-minus">−</button>
          <input type="number" name="quantity" id="qty" class="qty-input" value="1" min="1" max="99">
          <button type="button" class="qty-btn" id="qty-plus">+</button>
        </div>
        <button type="submit" class="btn btn--full btn--lg">🛒 Добавить в корзину</button>
      </form>
    <?php else: ?>
      <div class="notice notice--warn">Этот товар временно отсутствует. Оставьте заявку и мы сообщим о его появлении.</div>
      <a href="contacts.php" class="btn btn--full btn--dark">Оставить заявку</a>
    <?php endif; ?>

    <div style="margin-top:20px;display:flex;gap:24px;font-size:13px;color:var(--text-muted);">
      <span>🚚 Доставка по Казахстану</span>
      <span>🛡️ Гарантия 3 года</span>
    </div>
  </div>
</div>

<!-- RELATED -->
<?php
$related = getProducts(['category' => $p['cat_slug'], 'limit' => 4]);
$related = array_filter($related, function($r) use ($p) { return $r['id'] != $p['id']; });
if ($related):
?>
<div class="section--bg">
  <div class="section__inner">
    <div class="section__head">
      <h2 class="section__title">Похожие <em>товары</em></h2>
    </div>
    <div class="products-grid">
      <?php foreach (array_slice($related, 0, 4) as $p): ?>
        <?php include 'includes/product-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
