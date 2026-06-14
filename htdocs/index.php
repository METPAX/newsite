<?php
    ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/functions.php';
$pageTitle = 'ТОО «Колор» — Мебельный цех | Производство мебели в Алтае';
$featured = getProducts(['featured' => true, 'limit' => 8]);
$categories = getCategories();
include 'includes/header.php';
?>

<!-- HERO -->
<section class="hero">
  <div class="hero__inner">
    <div class="hero__tag fade-up">Мебельный цех ТОО «Колор»</div>
    <h1 class="fade-up fade-up-1">Мебель, созданная<br><em>с душой</em></h1>
    <p class="fade-up fade-up-2">Производим и продаём качественную мебель для дома и офиса с 2005 года. Индивидуальный подход, гарантия качества, доставка по Казахстану.</p>
    <div class="hero__btns fade-up fade-up-3">
      <a href="catalog.php" class="btn btn--lg">Смотреть каталог</a>
      <a href="contacts.php" class="btn btn--ghost btn--lg">Оставить заявку</a>
    </div>
    <div class="hero__stats fade-up">
      <div>
        <div class="hero__stat-num">20+</div>
        <div class="hero__stat-label">лет опыта</div>
      </div>
      <div>
        <div class="hero__stat-num">500+</div>
        <div class="hero__stat-label">довольных клиентов</div>
      </div>
      <div>
        <div class="hero__stat-num">100%</div>
        <div class="hero__stat-label">гарантия качества</div>
      </div>
      <div>
        <div class="hero__stat-num">12</div>
        <div class="hero__stat-label">категорий мебели</div>
      </div>
    </div>
  </div>
</section>

<!-- CATEGORIES -->
<div class="section">
  <div class="section__head">
    <div>
      <div class="section__subtitle">Ассортимент</div>
      <h2 class="section__title">Наши <em>категории</em></h2>
    </div>
    <a href="catalog.php" class="btn btn--ghost btn--dark">Весь каталог →</a>
  </div>
  <div class="cats-grid">
    <?php foreach ($categories as $cat): ?>
    <a href="catalog.php?category=<?= $cat['slug'] ?>" class="cat-card">
      <div class="cat-card__icon"><?= $cat['icon'] ?></div>
      <div class="cat-card__name"><?= htmlspecialchars($cat['name']) ?></div>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<!-- FEATURED PRODUCTS -->
<div class="section--bg">
  <div class="section__inner">
    <div class="section__head">
      <div>
        <div class="section__subtitle">Популярное</div>
        <h2 class="section__title">Хиты <em>продаж</em></h2>
      </div>
      <a href="catalog.php" class="btn btn--ghost btn--dark">Все товары →</a>
    </div>
    <div class="products-grid">
      <?php foreach ($featured as $p): ?>
      <?php include 'includes/product-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- WHY US -->
<div class="section">
  <div class="section__head">
    <div>
      <div class="section__subtitle">Преимущества</div>
      <h2 class="section__title">Почему <em>Колор</em>?</h2>
    </div>
  </div>
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:28px;">
    <?php
    $features = [
      ['🏭','Собственное производство','Изготавливаем мебель на нашем заводе в Алтае, без посредников'],
      ['🛡️','Гарантия 3 года','На всю продукцию предоставляем гарантию качества'],
      ['🚚','Доставка по КЗ','Доставим в любой город Казахстана в удобное для вас время'],
      ['📐','Индивидуальный заказ','Изготовим мебель по вашим размерам и пожеланиям'],
    ];
    foreach ($features as $f): ?>
    <div style="background:var(--white);border:1px solid var(--border);padding:32px 24px;border-radius:var(--radius);">
      <div style="font-size:40px;margin-bottom:16px;"><?= $f[0] ?></div>
      <h3 style="font-family:var(--font-display);font-size:20px;margin-bottom:8px;"><?= $f[1] ?></h3>
      <p style="font-size:13px;color:var(--text-muted);line-height:1.7;"><?= $f[2] ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- CTA -->
<div style="background:var(--brown);padding:80px 48px;text-align:center;">
  <div style="max-width:600px;margin:0 auto;">
    <div style="color:var(--gold-light);font-size:12px;letter-spacing:3px;text-transform:uppercase;margin-bottom:16px;">Специальное предложение</div>
    <h2 style="font-family:var(--font-display);font-size:48px;color:var(--cream);margin-bottom:16px;">Индивидуальный <em style="font-style:italic;color:var(--gold-light);">заказ</em></h2>
    <p style="color:rgba(245,240,232,.6);margin-bottom:32px;font-size:16px;">Проконсультируем, замеряем и изготовим мебель по вашему проекту. Оставьте заявку — мы свяжемся в течение часа.</p>
    <a href="contacts.php" class="btn btn--lg">Оставить заявку</a>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
