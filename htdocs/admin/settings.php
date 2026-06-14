<?php
require_once '../includes/functions.php';
if (!isAdmin()) { flash('Доступ запрещён', 'error'); redirect('../login.php'); }

$pageTitle = 'Настройки сайта — Администрирование';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("INSERT INTO public.settings (setting_key, setting_value) VALUES (?, ?) ON CONFLICT (setting_key) DO UPDATE SET setting_value = EXCLUDED.setting_value");
    
    $settings_to_update = [
        'site_name',
        'site_logo_text',
        'site_phone1',
        'site_phone2',
        'site_email1',
        'site_email2',
        'site_working_hours',
        'site_working_hours_short',
        'site_address',
        'site_address_region',
        'app_title',
        'app_description',
        'app_version',
        'app_features',
        'app_download_link',
    ];
    
    foreach ($settings_to_update as $key) {
        if (isset($_POST[$key])) {
            $stmt->execute([$key, trim($_POST[$key])]);
        }
    }
    flash('Настройки сайта успешно сохранены');
    redirect('settings.php');
}

include '../includes/header.php';
?>

<div class="admin-layout">
  <nav class="admin-sidebar">
    <div style="padding:0 24px 24px;color:rgba(245,240,232,.4);font-size:11px;letter-spacing:2px;text-transform:uppercase;">Панель управления</div>
    <a href="index.php">📊 Дашборд</a>
    <a href="products.php">📦 Товары</a>
    <a href="orders.php">🧾 Заказы</a>
    <a href="users.php">👥 Клиенты</a>
    <a href="settings.php" class="active">⚙️ Настройки</a>
    <a href="../index.php">← На сайт</a>
  </nav>

  <div class="admin-content">
    <h1>Настройки сайта</h1>
    
    <div style="background:var(--white);border:1px solid var(--border);padding:36px;border-radius:2px;">
      <form method="POST">
        
        <h3 style="font-family:var(--font-display);font-size:22px;margin-bottom:16px;border-bottom:1px solid var(--border);padding-bottom:8px;color:var(--brown);">🏢 Основная информация</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
          <div class="form-group">
            <label>Название организации *</label>
            <input type="text" name="site_name" required value="<?= htmlspecialchars(getSetting('site_name', 'ТОО «Колор»')) ?>">
          </div>
          <div class="form-group">
            <label>Текст логотипа (HTML разрешен) *</label>
            <input type="text" name="site_logo_text" required value="<?= htmlspecialchars(getSetting('site_logo_text', 'КОЛОР <em>мебель</em>')) ?>">
          </div>
        </div>

        <h3 style="font-family:var(--font-display);font-size:22px;margin-bottom:16px;border-bottom:1px solid var(--border);padding-bottom:8px;color:var(--brown);">📞 Контакты</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
          <div class="form-group">
            <label>Телефон (основной) *</label>
            <input type="text" name="site_phone1" required value="<?= htmlspecialchars(getSetting('site_phone1', '+7 (722) 200-00-00')) ?>">
          </div>
          <div class="form-group">
            <label>Телефон (дополнительный)</label>
            <input type="text" name="site_phone2" value="<?= htmlspecialchars(getSetting('site_phone2', '+7 (700) 200-00-00')) ?>">
          </div>
          <div class="form-group">
            <label>Email (основной) *</label>
            <input type="email" name="site_email1" required value="<?= htmlspecialchars(getSetting('site_email1', 'info@kolor.kz')) ?>">
          </div>
          <div class="form-group">
            <label>Email (для заказов)</label>
            <input type="email" name="site_email2" value="<?= htmlspecialchars(getSetting('site_email2', 'order@kolor.kz')) ?>">
          </div>
        </div>

        <h3 style="font-family:var(--font-display);font-size:22px;margin-bottom:16px;border-bottom:1px solid var(--border);padding-bottom:8px;color:var(--brown);">📍 Физический адрес</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
          <div class="form-group">
            <label>Адрес (улица, дом) *</label>
            <input type="text" name="site_address" required value="<?= htmlspecialchars(getSetting('site_address', 'г. Алтай, ул. Промышленная, 15')) ?>">
          </div>
          <div class="form-group">
            <label>Область / Регион</label>
            <input type="text" name="site_address_region" value="<?= htmlspecialchars(getSetting('site_address_region', 'Восточно-Казахстанская обл.')) ?>">
          </div>
        </div>

        <h3 style="font-family:var(--font-display);font-size:22px;margin-bottom:16px;border-bottom:1px solid var(--border);padding-bottom:8px;color:var(--brown);">⏰ Режим работы</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:32px;">
          <div class="form-group">
            <label>Режим работы (полный, с переносами строк) *</label>
            <textarea name="site_working_hours" rows="4" required><?= htmlspecialchars(getSetting('site_working_hours', "Пн–Пт: 9:00–18:00\nСуббота: 10:00–16:00\nВоскресенье: выходной")) ?></textarea>
          </div>
          <div class="form-group">
            <label>Режим работы (краткий, для футера) *</label>
            <input type="text" name="site_working_hours_short" required value="<?= htmlspecialchars(getSetting('site_working_hours_short', 'Пн–Сб: 9:00–18:00')) ?>">
          </div>
        </div>

        <h3 style="font-family:var(--font-display);font-size:22px;margin-bottom:16px;border-bottom:1px solid var(--border);padding-bottom:8px;color:var(--brown);">📱 Мобильное приложение</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
          <div class="form-group" style="grid-column: span 2;">
            <label>Ссылка на скачивание (поддерживаются ссылки Google Drive, которые автоматически конвертируются в прямые) *</label>
            <input type="url" name="app_download_link" required value="<?= htmlspecialchars(getSetting('app_download_link', 'https://drive.google.com/file/d/1kajcHIaRO3TlFz9vOYUJZ3I_dml8t2YB/view?usp=sharing')) ?>">
          </div>
          <div class="form-group">
            <label>Название приложения *</label>
            <input type="text" name="app_title" required value="<?= htmlspecialchars(getSetting('app_title', 'Мобильное приложение КОЛОР')) ?>">
          </div>
          <div class="form-group">
            <label>Версия приложения *</label>
            <input type="text" name="app_version" required value="<?= htmlspecialchars(getSetting('app_version', '1.0.0')) ?>">
          </div>
          <div class="form-group">
            <label>Описание приложения *</label>
            <textarea name="app_description" rows="4" required><?= htmlspecialchars(getSetting('app_description', 'Наше мобильное приложение поможет вам просматривать полный каталог изделий, делать быстрые заказы, отслеживать статус сборки/доставки и связываться с менеджером в один клик. Скачайте приложение прямо сейчас!')) ?></textarea>
          </div>
          <div class="form-group">
            <label>Функции приложения (каждая с новой строки) *</label>
            <textarea name="app_features" rows="4" required><?= htmlspecialchars(getSetting('app_features', "Каталог мебели в кармане\nБыстрый и удобный заказ\nУведомления о статусе сборки и доставки\nКалькулятор стоимости индивидуальных размеров\nПрямая связь с поддержкой")) ?></textarea>
          </div>
        </div>

        <button type="submit" class="btn btn--lg">💾 Сохранить настройки</button>
      </form>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
