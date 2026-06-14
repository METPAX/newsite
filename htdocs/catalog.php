<?php
require_once 'includes/functions.php';
$pageTitle = 'Каталог мебели — ТОО «Колор»';

$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

$filters = [];
if ($category) $filters['category'] = $category;
if ($search) $filters['search'] = $search;

$products = getProducts($filters);
$categories = getCategories();

include 'includes/header.php';
?>

<div class="page-hero">
  <h1>Каталог мебели</h1>
  <p>Выберите мебель по категории или воспользуйтесь поиском</p>
  <form class="search-bar" method="GET">
    <?php if($category): ?><input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>"><?php endif; ?>
    <input type="text" name="search" placeholder="Поиск по каталогу..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Найти</button>
  </form>
</div>

<!-- Мобильные pills-фильтры (видны только на телефоне) -->
<div style="padding:14px 16px 0;background:var(--cream);">
  <div class="cat-pills">
    <a href="catalog.php" class="cat-pill <?= !$category ? 'active' : '' ?>">🏠 Все</a>
    <?php foreach ($categories as $cat): ?>
    <a href="catalog.php?category=<?= $cat['slug'] ?><?= $search ? '&search='.urlencode($search) : '' ?>"
       class="cat-pill <?= $category === $cat['slug'] ? 'active' : '' ?>">
      <?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<div class="catalog-layout">
  <!-- SIDEBAR -->
  <aside class="sidebar">
    <h3>Категории</h3>
    <ul class="sidebar__cats">
      <li>
        <a href="catalog.php" class="<?= !$category ? 'active' : '' ?>">
          🏠 Все товары <span style="color:var(--text-muted);font-size:12px;">(<?= count(getProducts()) ?>)</span>
        </a>
      </li>
      <?php foreach ($categories as $cat): ?>
      <?php $cnt = count(getProducts(['category' => $cat['slug']])); ?>
      <li>
        <a href="catalog.php?category=<?= $cat['slug'] ?><?= $search ? '&search='.urlencode($search) : '' ?>"
           class="<?= $category === $cat['slug'] ? 'active' : '' ?>">
          <?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?>
          <span style="color:var(--text-muted);font-size:12px;">(<?= $cnt ?>)</span>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
  </aside>

  <!-- PRODUCTS -->
  <div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
      <p style="color:var(--text-muted);font-size:14px;">
        <?php if($search): ?>Результаты поиска «<strong><?= htmlspecialchars($search) ?></strong>»: <?php endif; ?>
        Найдено товаров: <strong><?= count($products) ?></strong>
      </p>
      <?php if($search || $category): ?>
        <a href="catalog.php" style="font-size:13px;color:var(--gold);">✕ Сбросить фильтры</a>
      <?php endif; ?>
    </div>

    <?php if (empty($products)): ?>
      <div class="empty-state">
        <div class="empty-state__icon">🔍</div>
        <h3>Товары не найдены</h3>
        <p>Попробуйте изменить параметры поиска</p>
        <a href="catalog.php" class="btn">Весь каталог</a>
      </div>
    <?php else: ?>
      <div class="products-grid">
        <?php foreach ($products as $p): ?>
          <?php include 'includes/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
