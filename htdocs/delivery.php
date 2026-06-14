<?php
require_once 'includes/functions.php';
$pageTitle = 'Доставка и оплата — ' . htmlspecialchars(getSetting('site_name', 'ТОО «Колор»'));
include 'includes/header.php';
?>

<div class="page-hero">
  <h1>Доставка и оплата</h1>
  <p>Доставим вашу мебель в любую точку Казахстана</p>
</div>

<div class="section" style="max-width:900px;">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:40px;margin-bottom:60px;">
    <!-- DELIVERY -->
    <div>
      <div class="section__subtitle">Доставка</div>
      <h2 class="section__title" style="margin-bottom:28px;"><em>Сроки</em> и стоимость</h2>
      <div style="display:flex;flex-direction:column;gap:16px;">
        <div style="background:var(--white);border:1px solid var(--border);padding:20px 24px;border-radius:2px;">
          <div style="font-weight:600;margin-bottom:4px;">🚚 По городу Алтай</div>
          <div style="font-size:13px;color:var(--text-muted);">1–2 рабочих дня</div>
          <div style="color:var(--gold);font-weight:600;margin-top:6px;">Бесплатно при заказе от 50 000 ₸</div>
        </div>
        <div style="background:var(--white);border:1px solid var(--border);padding:20px 24px;border-radius:2px;">
          <div style="font-weight:600;margin-bottom:4px;">📦 По Казахстану</div>
          <div style="font-size:13px;color:var(--text-muted);">3–7 рабочих дней</div>
          <div style="color:var(--gold);font-weight:600;margin-top:6px;">Бесплатно при заказе от 100 000 ₸</div>
        </div>
        <div style="background:var(--white);border:1px solid var(--border);padding:20px 24px;border-radius:2px;">
          <div style="font-weight:600;margin-bottom:4px;">🏠 Подъём и сборка</div>
          <div style="font-size:13px;color:var(--text-muted);">Услуга предоставляется по запросу</div>
          <div style="color:var(--text-muted);font-size:13px;margin-top:6px;">Стоимость уточняется у менеджера</div>
        </div>
      </div>
    </div>

    <!-- PAYMENT -->
    <div>
      <div class="section__subtitle">Оплата</div>
      <h2 class="section__title" style="margin-bottom:28px;">Способы <em>оплаты</em></h2>
      <div style="display:flex;flex-direction:column;gap:16px;">
        <div style="background:var(--white);border:1px solid var(--border);padding:20px 24px;border-radius:2px;">
          <div style="font-weight:600;margin-bottom:4px;">💵 Наличными</div>
          <div style="font-size:13px;color:var(--text-muted);">При получении товара или в нашем офисе</div>
        </div>
        <div style="background:var(--white);border:1px solid var(--border);padding:20px 24px;border-radius:2px;">
          <div style="font-weight:600;margin-bottom:4px;">💳 Банковской картой</div>
          <div style="font-size:13px;color:var(--text-muted);">Visa, Mastercard, Kaspi при получении</div>
        </div>
        <div style="background:var(--white);border:1px solid var(--border);padding:20px 24px;border-radius:2px;">
          <div style="font-weight:600;margin-bottom:4px;">📱 Kaspi Pay / QR</div>
          <div style="font-size:13px;color:var(--text-muted);">Удобная оплата через приложение Kaspi</div>
        </div>
        <div style="background:var(--white);border:1px solid var(--border);padding:20px 24px;border-radius:2px;">
          <div style="font-weight:600;margin-bottom:4px;">🏦 Безналичный расчёт</div>
          <div style="font-size:13px;color:var(--text-muted);">Для юридических лиц. Выставляем счёт.</div>
        </div>
      </div>
    </div>
  </div>

  <div class="notice notice--info">
    <strong>Важно:</strong> При оформлении индивидуального заказа берётся предоплата 50%. Остаток оплачивается при получении готового изделия.
  </div>
</div>

<?php include 'includes/footer.php'; ?>
