<?php
$_imgFile = $p['image'] ?? '';
$_imgAbs  = dirname(__DIR__) . '/assets/img/products/' . $_imgFile;
$_imgSrc  = '/assets/img/products/' . $_imgFile;
$_hasImg  = $_imgFile && $_imgFile !== 'default.jpg' && file_exists($_imgAbs);

$_icons = ['sofa'=>'🛋️','bed'=>'🛏️','kitchen'=>'🍽️','wardrobe'=>'🗄️',
           'table'=>'🪑','chair'=>'🪑','shelf'=>'📚','armchair'=>'💺','kids'=>'🧸'];
$_icon = '🛋️';
foreach($_icons as $_k=>$_v) if(strpos($_imgFile, $_k)!==false){$_icon=$_v;break;}
?>
<div class="product-card">
  <a href="/product.php?id=<?= $p['id'] ?>">
    <div class="product-card__img">
      <?php if($p['old_price']): ?>
        <div class="product-card__badge">Акция</div>
      <?php endif; ?>
      <?php if($_hasImg): ?>
        <img src="<?= htmlspecialchars($_imgSrc) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
      <?php else: ?>
        <span style="font-size:72px;"><?= $_icon ?></span>
      <?php endif; ?>
    </div>
  </a>
  <div class="product-card__body">
    <div class="product-card__cat"><?= htmlspecialchars($p['cat_name'] ?? '') ?></div>
    <a href="/product.php?id=<?= $p['id'] ?>">
      <h3 class="product-card__name"><?= htmlspecialchars($p['name']) ?></h3>
    </a>
    <p class="product-card__desc"><?= htmlspecialchars($p['description'] ?? '') ?></p>
    <div class="product-card__footer">
      <div class="price">
        <span class="price__current"><?= formatPrice($p['price']) ?></span>
        <?php if($p['old_price']): ?>
          <span class="price__old"><?= formatPrice($p['old_price']) ?></span>
        <?php endif; ?>
      </div>
      <?php if($p['in_stock']): ?>
        <form method="POST" action="/cart-action.php">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
          <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
          <button type="submit" class="btn btn--sm">В корзину</button>
        </form>
      <?php else: ?>
        <span style="font-size:12px;color:var(--text-muted);">Нет в наличии</span>
      <?php endif; ?>
    </div>
  </div>
</div>