<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? (htmlspecialchars(getSetting('site_name', 'ТОО «Колор»')) . ' — Мебельный цех') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>

<?php $flash = getFlash(); if($flash): ?>
<div class="flash flash--<?= $flash['type'] ?>" id="flashMsg">
  <?= htmlspecialchars($flash['msg']) ?>
  <button onclick="this.parentElement.remove()" class="flash-close">✕</button>
</div>
<script>
  // Убираем через 3 сек — inline, без ожидания DOMContentLoaded
  (function(){
    var el = document.getElementById('flashMsg');
    if(!el) return;
    setTimeout(function(){
      el.style.transition = 'opacity 0.4s, transform 0.4s';
      el.style.opacity = '0';
      el.style.transform = 'translateX(-50%) translateY(10px)';
      setTimeout(function(){ el.remove(); }, 400);
    }, 3000);
  })();
</script>
<?php endif; ?>

<header class="header">
  <div class="header__top">

    <a href="/index.php" class="logo">
      <span class="logo__icon">◆</span>
      <span class="logo__text"><?= getSetting('site_logo_text', 'КОЛОР <em>мебель</em>') ?></span>
    </a>

    <nav class="header__nav">
      <a href="/index.php">Главная</a>
      <a href="/catalog.php">Каталог</a>
      <a href="/about.php">О нас</a>
      <a href="/contacts.php">Контакты</a>
      <a href="/delivery.php">Доставка</a>
      <a href="/app.php">Приложение</a>
    </nav>

    <div class="header__actions">
      <?php if (isLoggedIn()): ?>
        <a href="/profile.php" class="btn-icon" title="Профиль">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="12" cy="8" r="4"/>
            <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
          </svg>
        </a>
        <a href="/cart.php" class="btn-icon btn-cart" title="Корзина">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <path d="M16 10a4 4 0 01-8 0"/>
          </svg>
          <?php $cc = getCartCount(); if($cc > 0): ?>
            <span class="badge"><?= $cc ?></span>
          <?php endif; ?>
        </a>
        <?php if(isAdmin()): ?>
          <a href="/admin/index.php" class="btn btn--sm">Админ</a>
        <?php endif; ?>
        <a href="/logout.php" class="btn btn--ghost btn--sm">Выйти</a>
      <?php else: ?>
        <a href="/login.php" class="btn btn--ghost btn--sm">Войти</a>
        <a href="/register.php" class="btn btn--sm">Регистрация</a>
      <?php endif; ?>
    </div>

    <button class="burger" id="burgerBtn" aria-label="Меню" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>

  </div>
</header>

<nav class="mobile-nav" id="mobileNav" aria-hidden="true">
  <a href="/index.php">🏠 Главная</a>
  <a href="/catalog.php">🪑 Каталог</a>
  <a href="/about.php">🏭 О нас</a>
  <a href="/delivery.php">🚚 Доставка и оплата</a>
  <a href="/contacts.php">📞 Контакты</a>
  <a href="/app.php">📱 Приложение</a>
  <div class="mobile-nav__divider"></div>
  <?php if(isLoggedIn()): ?>
    <a href="/cart.php">🛒 Корзина<?php $cc=getCartCount(); if($cc>0) echo " ($cc)"; ?></a>
    <a href="/profile.php">👤 Личный кабинет</a>
    <?php if(isAdmin()): ?><a href="/admin/index.php">⚙️ Панель администратора</a><?php endif; ?>
    <a href="/logout.php">🚪 Выйти</a>
  <?php else: ?>
    <a href="/login.php">🔑 Войти</a>
    <a href="/register.php">✍️ Регистрация</a>
  <?php endif; ?>
</nav>

<main class="main">
