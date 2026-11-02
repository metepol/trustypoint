/* TrustyPoint — arayüz etkileşimleri (bağımlılıksız) */
(() => {
  'use strict';
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));

  // Mobil menü
  const burger = $('.site-header .burger');
  const nav = $('#main-nav');
  if (burger && nav) {
    burger.addEventListener('click', () => {
      nav.style.top = Math.max(0, burger.closest('.site-header').getBoundingClientRect().bottom) + 'px';
      const open = nav.classList.toggle('is-open');
      burger.setAttribute('aria-expanded', String(open));
      document.body.classList.toggle('nav-open', open);
    });
  }

  // Yönetim menüsü
  const admToggle = $('[data-adm-toggle]');
  const admSide = $('#adm-side');
  if (admToggle && admSide) {
    admToggle.addEventListener('click', (e) => { e.stopPropagation(); admSide.classList.toggle('is-open'); });
    document.addEventListener('click', (e) => { if (!admSide.contains(e.target)) admSide.classList.remove('is-open'); });
  }

  // Bildirimler
  $$('.toast').forEach((t, i) => {
    const close = () => { t.style.opacity = '0'; t.style.transform = 'translateY(-8px)'; setTimeout(() => t.remove(), 250); };
    $('.toast-x', t)?.addEventListener('click', close);
    if (!t.classList.contains('toast-error')) setTimeout(close, 6000 + i * 800);
  });

  // Görünür olunca içeri kay
  const reveals = $$('.reveal');
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((en) => { if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); } });
    }, { rootMargin: '0px 0px -60px 0px' });
    reveals.forEach((el, i) => { el.style.transitionDelay = (i % 3) * 80 + 'ms'; io.observe(el); });
  } else {
    reveals.forEach((el) => el.classList.add('is-in'));
  }

  // Geri sayım
  const pad = (n) => String(n).padStart(2, '0');
  $$('[data-countdown]').forEach((el) => {
    const target = new Date(el.dataset.countdown).getTime();
    const parts = { d: $('[data-cd="d"]', el), h: $('[data-cd="h"]', el), m: $('[data-cd="m"]', el), s: $('[data-cd="s"]', el) };
    const tick = () => {
      let diff = Math.max(0, Math.floor((target - Date.now()) / 1000));
      if (diff === 0) { el.classList.add('is-live'); return false; }
      const d = Math.floor(diff / 86400); diff -= d * 86400;
      const h = Math.floor(diff / 3600); diff -= h * 3600;
      const m = Math.floor(diff / 60); const s = diff - m * 60;
      if (parts.d) parts.d.textContent = pad(d);
      if (parts.h) parts.h.textContent = pad(h);
      if (parts.m) parts.m.textContent = pad(m);
      if (parts.s) parts.s.textContent = pad(s);
      return true;
    };
    if (tick()) { const id = setInterval(() => { if (!tick()) clearInterval(id); }, 1000); }
  });

  // Şifre göster/gizle
  $$('[data-toggle-pass]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const input = btn.parentElement.querySelector('input');
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.setAttribute('aria-label', show ? 'Şifreyi gizle' : 'Şifreyi göster');
    });
  });

  // Kopyala
  $$('[data-copy]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const src = $(btn.dataset.copy);
      if (!src) return;
      try { await navigator.clipboard.writeText(src.textContent.trim()); const o = btn.textContent; btn.textContent = 'Kopyalandı ✓'; setTimeout(() => (btn.textContent = o), 1600); } catch (e) { /* yok say */ }
    });
  });

  // Hero tanıtım videosu
  $$('[data-hv-open]').forEach((btn) => btn.addEventListener('click', () => {
    const box = btn.closest('.hero-video');
    const f = document.createElement('iframe');
    f.src = box.dataset.hvEmbed;
    f.title = 'Tanıtım videosu';
    f.allow = 'autoplay; fullscreen; picture-in-picture; encrypted-media';
    f.allowFullscreen = true;
    box.appendChild(f);
    box.classList.add('is-playing');
  }));
  $$('[data-hv-sound]').forEach((btn) => btn.addEventListener('click', () => {
    const box = btn.closest('.hero-video');
    const v = box.querySelector('video');
    v.muted = false; v.loop = false; v.controls = true; v.currentTime = 0;
    v.play().catch(() => {});
    box.classList.add('is-playing');
  }));

  // Onay isteyen formlar
  $$('form[data-confirm]').forEach((f) => f.addEventListener('submit', (e) => { if (!confirm(f.dataset.confirm)) e.preventDefault(); }));

  // Çift gönderimi engelle
  $$('form').forEach((f) => f.addEventListener('submit', (e) => {
    setTimeout(() => { if (!e.defaultPrevented) $$('button:not([type="button"])', f).forEach((b) => { b.disabled = true; }); }, 0);
  }));
})();
