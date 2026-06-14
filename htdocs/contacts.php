<?php
require_once 'includes/functions.php';
$pageTitle = 'Контакты — ' . htmlspecialchars(getSetting('site_name', 'ТОО «Колор»'));
$sent = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cname = trim($_POST['cname'] ?? '');
    $cphone = trim($_POST['cphone'] ?? '');
    $cmsg = trim($_POST['cmsg'] ?? '');
    if (!$cname) $errors[] = 'Укажите имя';
    if (!$cphone) $errors[] = 'Укажите телефон';
    if (!$cmsg) $errors[] = 'Напишите сообщение';
    if (empty($errors)) {
        // В реальном проекте здесь отправка email
        $sent = true;
    }
}
include 'includes/header.php';
?>

<div class="page-hero">
  <h1>Контакты</h1>
  <p>Свяжитесь с нами — ответим в течение рабочего часа</p>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:60px;max-width:1100px;margin:0 auto;padding:60px 48px;">
  <!-- CONTACT INFO -->
  <div>
    <div class="section__subtitle">Наши контакты</div>
    <h2 class="section__title" style="margin-bottom:36px;">Мы всегда <em>на связи</em></h2>

    <div style="display:flex;flex-direction:column;gap:20px;">
      <?php
      $address_line = htmlspecialchars(getSetting('site_address', 'г. Алтай, ул. Промышленная, 15'));
      $region = getSetting('site_address_region', 'Восточно-Казахстанская обл.');
      if ($region) {
          $address_line .= '<br>' . htmlspecialchars($region);
      }

      $phone1 = getSetting('site_phone1', '+7 (722) 200-00-00');
      $phone2 = getSetting('site_phone2', '+7 (700) 200-00-00');
      $phone_line = '<a href="tel:' . preg_replace('/[^0-9+]/', '', $phone1) . '">' . htmlspecialchars($phone1) . '</a>';
      if ($phone2) {
          $phone_line .= '<br><a href="tel:' . preg_replace('/[^0-9+]/', '', $phone2) . '">' . htmlspecialchars($phone2) . '</a>';
      }

      $email1 = getSetting('site_email1', 'info@kolor.kz');
      $email2 = getSetting('site_email2', 'order@kolor.kz');
      $email_line = '<a href="mailto:' . htmlspecialchars($email1) . '">' . htmlspecialchars($email1) . '</a>';
      if ($email2) {
          $email_line .= '<br><a href="mailto:' . htmlspecialchars($email2) . '">' . htmlspecialchars($email2) . '</a>';
      }

      $working_hours = getSetting('site_working_hours', "Пн–Пт: 9:00–18:00\nСуббота: 10:00–16:00\nВоскресенье: выходной");
      $working_hours_line = nl2br(htmlspecialchars($working_hours));

      $contacts = [
        ['📍','Адрес', $address_line],
        ['📞','Телефон', $phone_line],
        ['📧','Email', $email_line],
        ['⏰','Режим работы', $working_hours_line],
      ];
      foreach ($contacts as $c): ?>
      <div style="display:flex;gap:16px;background:var(--white);border:1px solid var(--border);padding:20px 24px;border-radius:2px;">
        <div style="font-size:28px;flex-shrink:0;"><?= $c[0] ?></div>
        <div>
          <div style="font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:var(--text-muted);margin-bottom:4px;"><?= $c[1] ?></div>
          <div style="font-size:15px;line-height:1.7;"><?= $c[2] ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- CONTACT FORM -->
  <div>
    <div class="section__subtitle">Обратная связь</div>
    <h2 class="section__title" style="margin-bottom:36px;">Оставить <em>заявку</em></h2>

    <?php if ($sent): ?>
      <div style="background:var(--white);border:1px solid var(--border);padding:40px;text-align:center;border-radius:2px;">
        <div style="font-size:52px;margin-bottom:16px;">✅</div>
        <h3 style="font-family:var(--font-display);font-size:28px;margin-bottom:8px;">Заявка отправлена!</h3>
        <p style="color:var(--text-muted);">Мы свяжемся с вами в ближайшее время.</p>
      </div>
    <?php else: ?>
      <?php if($errors): ?>
        <div class="notice notice--warn">
          <?php foreach($errors as $e): ?><div>⚠️ <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
        </div>
      <?php endif; ?>
      <div style="background:var(--white);border:1px solid var(--border);padding:36px;border-radius:2px;">
        <form method="POST">
          <div class="form-group">
            <label>Ваше имя *</label>
            <input type="text" name="cname" required value="<?= htmlspecialchars($_POST['cname'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Телефон *</label>
            <input type="tel" name="cphone" placeholder="+7 (___) ___-__-__" required value="<?= htmlspecialchars($_POST['cphone'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Сообщение *</label>
            <textarea name="cmsg" rows="5" placeholder="Опишите, что вас интересует..."><?= htmlspecialchars($_POST['cmsg'] ?? '') ?></textarea>
          </div>
          <button type="submit" class="btn btn--full btn--lg">Отправить заявку</button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
