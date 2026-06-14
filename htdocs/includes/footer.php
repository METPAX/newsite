</main>

<footer class="footer">
  <div class="footer__inner">
    <div class="footer__brand">
      <div class="logo">
        <span class="logo__icon">◆</span>
        <span class="logo__text"><?= getSetting('site_logo_text', 'КОЛОР <em>мебель</em>') ?></span>
      </div>
      <p>Мебельный цех <?= htmlspecialchars(getSetting('site_name', 'ТОО «Колор»')) ?> — производство и продажа мебели с 2005 года. Качество, проверенное временем.</p>
    </div>
    <div class="footer__col">
      <h4>Каталог</h4>
      <ul>
        <?php foreach(getCategories() as $cat): ?>
        <li><a href="catalog.php?category=<?= $cat['slug'] ?>"><?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="footer__col">
      <h4>Информация</h4>
      <ul>
        <li><a href="about.php">О компании</a></li>
        <li><a href="contacts.php">Контакты</a></li>
        <li><a href="delivery.php">Доставка и оплата</a></li>
        <li><a href="app.php">Мобильное приложение</a></li>
      </ul>
    </div>
    <div class="footer__col">
      <h4>Контакты</h4>
      <p>📍 <?= htmlspecialchars(getSetting('site_address', 'г. Алтай, ул. Промышленная, 15')) ?></p>
      <?php $phone1 = getSetting('site_phone1', '+7 (722) 200-00-00'); ?>
      <p>📞 <a href="tel:<?= preg_replace('/[^0-9+]/', '', $phone1) ?>"><?= htmlspecialchars($phone1) ?></a></p>
      <?php $email1 = getSetting('site_email1', 'info@kolor.kz'); ?>
      <p>📧 <a href="mailto:<?= htmlspecialchars($email1) ?>"><?= htmlspecialchars($email1) ?></a></p>
      <p>⏰ <?= htmlspecialchars(getSetting('site_working_hours_short', 'Пн–Сб: 9:00–18:00')) ?></p>
    </div>
  </div>
  <div class="footer__bottom">
    <p>© <?= date('Y') ?> <?= htmlspecialchars(getSetting('site_name', 'ТОО «Колор»')) ?>. Все права защищены.</p>
  </div>
</footer>

<script src="/assets/js/main.js"></script>
</body>
</html>
