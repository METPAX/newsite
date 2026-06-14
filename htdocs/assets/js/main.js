document.addEventListener('DOMContentLoaded', function () {

  /* ── Burger / мобильное меню ── */
  var burger    = document.getElementById('burgerBtn');
  var mobileNav = document.getElementById('mobileNav');
  var body      = document.body;

  function openNav() {
    burger.classList.add('open');
    mobileNav.classList.add('open');
    burger.setAttribute('aria-expanded', 'true');
    body.style.overflow = 'hidden';
  }
  function closeNav() {
    burger.classList.remove('open');
    mobileNav.classList.remove('open');
    burger.setAttribute('aria-expanded', 'false');
    body.style.overflow = '';
  }

  if (burger) {
    burger.addEventListener('click', function (e) {
      e.stopPropagation();
      burger.classList.contains('open') ? closeNav() : openNav();
    });
  }

  // Клик по ссылке в мобильном меню — закрыть
  if (mobileNav) {
    mobileNav.querySelectorAll('a').forEach(function(a) {
      a.addEventListener('click', closeNav);
    });
    // Клик вне меню — закрыть
    document.addEventListener('click', function (e) {
      if (mobileNav.classList.contains('open') &&
          !mobileNav.contains(e.target) &&
          !burger.contains(e.target)) {
        closeNav();
      }
    });
  }

  // Escape — закрыть
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeNav();
  });

  /* ── Tabs в профиле ── */
  document.querySelectorAll('.tab-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.tab-btn, .tab-panel')
        .forEach(function (el) { el.classList.remove('active'); });
      btn.classList.add('active');
      var panel = document.getElementById(btn.dataset.tab);
      if (panel) panel.classList.add('active');
    });
  });

  /* ── Кнопки +/− количества товара ── */
  var minusBtn = document.getElementById('qty-minus');
  var plusBtn  = document.getElementById('qty-plus');
  var qtyInput = document.getElementById('qty');
  if (minusBtn && qtyInput) {
    minusBtn.addEventListener('click', function () {
      if (+qtyInput.value > 1) qtyInput.value = +qtyInput.value - 1;
    });
  }
  if (plusBtn && qtyInput) {
    plusBtn.addEventListener('click', function () {
      qtyInput.value = +qtyInput.value + 1;
    });
  }

});
