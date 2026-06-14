<?php
require_once 'includes/functions.php';
$pageTitle = getSetting('app_title', 'Мобильное приложение КОЛОР') . ' — ТОО «Колор»';
include 'includes/header.php';

$appTitle = getSetting('app_title', 'Мобильное приложение КОЛОР');
$appDesc = getSetting('app_description', 'Наше мобильное приложение поможет вам просматривать полный каталог изделий, делать быстрые заказы, отслеживать статус сборки/доставки и связываться с менеджером в один клик.');
$appVersion = getSetting('app_version', '1.0.0');
$appDownload = getSetting('app_download_link', 'https://drive.google.com/file/d/1kajcHIaRO3TlFz9vOYUJZ3I_dml8t2YB/view?usp=sharing');
$directDownload = getGoogleDriveDirectLink($appDownload);

$featuresText = getSetting('app_features', "Каталог мебели в кармане\nБыстрый и удобный заказ\nУведомления о статусе сборки и доставки\nКалькулятор стоимости индивидуальных размеров\nПрямая связь с поддержкой");
$features = array_filter(array_map('trim', explode("\n", $featuresText)));
?>

<div class="page-hero">
  <h1>Мобильное приложение</h1>
  <p>Все возможности нашего мебельного цеха теперь в вашем смартфоне</p>
</div>

<div class="section" style="max-width:1100px; padding: 80px 48px;">
  <div style="display:grid;grid-template-columns: 1fr 1.2fr; gap:60px; align-items:center;">
    
    <!-- CSS PHONE MOCKUP -->
    <div style="display:flex; justify-content:center; perspective: 1000px;">
      <div class="phone-mockup" style="
        width: 300px;
        height: 600px;
        background: #111;
        border: 12px solid #2d231e;
        border-radius: 40px;
        box-shadow: 0 20px 50px rgba(61,43,31,0.25), 0 0 0 4px #b8860b;
        position: relative;
        overflow: hidden;
        animation: floatAnimation 6s ease-in-out infinite;
      ">
        <!-- Speaker / Notch -->
        <div style="
          width: 120px;
          height: 25px;
          background: #2d231e;
          position: absolute;
          top: 0;
          left: 50%;
          transform: translateX(-50%);
          border-bottom-left-radius: 18px;
          border-bottom-right-radius: 18px;
          z-index: 10;
          display: flex;
          align-items: center;
          justify-content: center;
        ">
          <div style="width: 40px; height: 4px; background: #555; border-radius: 2px;"></div>
        </div>
        
        <!-- Screen Content -->
        <div style="
          width: 100%;
          height: 100%;
          background: linear-gradient(135deg, #3d2b1f 0%, #1e130c 100%);
          padding: 40px 20px 20px;
          display: flex;
          flex-direction: column;
          justify-content: space-between;
          color: #f5f0e8;
          position: relative;
        ">
          <!-- App Logo -->
          <div style="text-align: center; margin-top: 40px;">
            <div style="font-size: 52px; margin-bottom: 12px; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));">📱</div>
            <div style="font-family: var(--font-display); font-size: 26px; font-weight: 600; color: var(--gold-light); letter-spacing: 2px; text-transform: uppercase;">
              КОЛОР
            </div>
            <div style="font-size: 11px; letter-spacing: 3px; color: rgba(245,240,232,0.6); text-transform: uppercase;">МЕБЕЛЬНЫЙ ЦЕХ</div>
          </div>
          
          <!-- App Mock Interface Details -->
          <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); padding: 18px; border-radius: 12px; margin-bottom: 20px; backdrop-filter: blur(10px);">
            <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: var(--gold-light); margin-bottom: 8px; font-weight: 600;">В приложении:</div>
            <div style="font-size: 13px; line-height: 1.6; display: flex; flex-direction: column; gap: 8px;">
              <div>✨ Удобный заказ по размерам</div>
              <div>💬 Консультация дизайнера</div>
              <div>🚚 Трекинг доставки</div>
            </div>
          </div>
          
          <!-- Download Hint -->
          <div style="text-align: center; padding-bottom: 20px;">
            <div style="display: inline-block; width: 50px; height: 50px; border-radius: 50%; background: var(--gold); color: var(--brown); font-size: 22px; line-height: 50px; text-align: center; margin-bottom: 10px; animation: pulse 2s infinite;">⬇️</div>
            <div style="font-size: 12px; color: rgba(245,240,232,0.6); text-transform: uppercase; letter-spacing: 1px;">Скачайте прямо сейчас</div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- APP INFO & DOWNLOAD BUTTON -->
    <div>
      <div class="section__subtitle">Официальное приложение</div>
      <h2 class="section__title" style="margin-bottom: 16px; font-size: 38px; line-height: 1.2;"><?= htmlspecialchars($appTitle) ?></h2>
      
      <div style="display: inline-block; background: var(--cream-dark); border: 1px solid var(--border); padding: 4px 12px; border-radius: 20px; font-size: 12px; color: var(--brown); font-weight: 600; margin-bottom: 28px; text-transform: uppercase; letter-spacing: 1px;">
        Версия <?= htmlspecialchars($appVersion) ?>
      </div>
      
      <p style="font-size: 16px; line-height: 1.8; color: var(--text-muted); margin-bottom: 32px;">
        <?= htmlspecialchars($appDesc) ?>
      </p>
      
      <div style="margin-bottom: 40px;">
        <h4 style="font-family: var(--font-display); font-size: 22px; color: var(--brown); margin-bottom: 18px; font-weight: 600;">Основные возможности:</h4>
        <ul style="display: flex; flex-direction: column; gap: 14px;">
          <?php foreach ($features as $f): ?>
          <li style="display: flex; align-items: center; gap: 12px; font-size: 15px; color: var(--text);">
            <span style="color: var(--gold); font-size: 18px;">✔</span>
            <span><?= htmlspecialchars($f) ?></span>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
      
      <div>
        <a href="<?= htmlspecialchars($directDownload) ?>" class="btn btn--lg" style="padding: 16px 40px; font-size: 15px; border-radius: 4px; box-shadow: 0 4px 14px rgba(184,134,11,0.4);" download>
          📥 Скачать приложение (.apk)
        </a>
        <div style="font-size: 12px; color: var(--text-muted); margin-top: 10px; padding-left: 4px;">
          Совместимо с Android 8.0 и выше. Безопасная прямая загрузка.
        </div>
      </div>
    </div>

  </div>
</div>

<style>
@keyframes floatAnimation {
  0% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-15px) rotate(1deg); }
  100% { transform: translateY(0px) rotate(0deg); }
}
@keyframes pulse {
  0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(184,134,11,0.7); }
  70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(184,134,11,0); }
  100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(184,134,11,0); }
}

@media (max-width: 768px) {
  .section > div { grid-template-columns: 1fr !important; gap: 40px !important; }
  .phone-mockup { width: 260px !important; height: 520px !important; }
}
</style>

<?php include 'includes/footer.php'; ?>
