<?php
require_once 'includes/functions.php';
$pageTitle = 'О компании — ' . htmlspecialchars(getSetting('site_name', 'ТОО «Колор»'));
include 'includes/header.php';
?>

<div class="page-hero">
  <h1>О компании</h1>
  <p>ТОО «Колор» — ваш надёжный производитель мебели с 2005 года</p>
</div>

<div class="section" style="max-width:900px;">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;margin-bottom:80px;">
    <div>
      <div class="section__subtitle">Наша история</div>
      <h2 class="section__title">Мебель с <em>душой</em></h2>
      <p style="color:var(--text-muted);line-height:1.9;margin-bottom:16px;">
        ТОО «Колор» основано в 2005 году в городе Алтай Восточно-Казахстанской области. За 20 лет работы мы превратились из небольшой мастерской в полноценный мебельный цех с собственным производством.
      </p>
      <p style="color:var(--text-muted);line-height:1.9;">
        Мы производим корпусную и мягкую мебель для жилых и офисных пространств, используя качественные материалы отечественных и европейских производителей.
      </p>
    </div>
    <div style="background:var(--brown);padding:48px;text-align:center;border-radius:2px;">
      <div style="font-size:80px;margin-bottom:16px;">🏭</div>
      <div style="font-family:var(--font-display);font-size:60px;color:var(--gold-light);font-weight:600;">20+</div>
      <div style="color:rgba(245,240,232,.6);font-size:14px;letter-spacing:2px;text-transform:uppercase;">лет опыта</div>
    </div>
  </div>

  <!-- VALUES -->
  <div class="section__subtitle">Наши ценности</div>
  <h2 class="section__title" style="margin-bottom:40px;">Что нас <em>отличает</em></h2>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:80px;">
    <?php
    $values = [
      ['🌲','Натуральные материалы','Используем экологически чистые материалы: массив дерева, МДФ, ЛДСП ведущих производителей'],
      ['✋','Ручная работа','Каждое изделие проходит через руки опытных мастеров с многолетним стажем'],
      ['📐','Точность','Производство с точностью до миллиметра. Каждый элемент подгоняется идеально'],
      ['🔄','Индивидуальный подход','Принимаем заказы по вашим размерам и предпочтениям — воплощаем любые идеи'],
    ];
    foreach ($values as $v): ?>
    <div style="background:var(--white);border:1px solid var(--border);padding:28px;border-radius:2px;">
      <div style="font-size:36px;margin-bottom:12px;"><?= $v[0] ?></div>
      <h3 style="font-family:var(--font-display);font-size:22px;margin-bottom:8px;"><?= $v[1] ?></h3>
      <p style="font-size:13px;color:var(--text-muted);line-height:1.8;"><?= $v[2] ?></p>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- TEAM -->
  <div class="section__subtitle">Команда</div>
  <h2 class="section__title" style="margin-bottom:40px;">Наши <em>специалисты</em></h2>
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:24px;">
    <?php
    $team = [
      ['👨‍💼','Асхат Сейткали','Директор','Основатель компании, 20 лет в мебельном производстве'],
      ['👩‍🎨','Гульнара Иванова','Дизайнер','Разрабатывает проекты и подбирает материалы'],
      ['👨‍🔧','Серик Ахметов','Мастер производства','Руководит цехом, 15 лет опыта'],
    ];
    foreach ($team as $t): ?>
    <div style="background:var(--white);border:1px solid var(--border);padding:28px;text-align:center;border-radius:2px;">
      <div style="font-size:52px;margin-bottom:12px;"><?= $t[0] ?></div>
      <div style="font-family:var(--font-display);font-size:20px;margin-bottom:4px;"><?= $t[1] ?></div>
      <div style="font-size:12px;letter-spacing:1px;text-transform:uppercase;color:var(--gold);margin-bottom:10px;"><?= $t[2] ?></div>
      <p style="font-size:13px;color:var(--text-muted);"><?= $t[3] ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
