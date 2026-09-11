/* Guru theme — front-end behaviour. Mirrors the inline scripts of the static site. */

/* ── Showcase lightbox ──
 * Two markups share one controller: the homepage modal (#hwVmodal, styled in
 * style.css) and the Work archive modal (#vmodal, styled in work.css) — the
 * same two designs the static site has. Cards open whichever is on the page. */
function guruModal(ids) {
  var overlay = document.getElementById(ids.overlay);
  if (!overlay) return null;
  var $ = function (id) { return id ? document.getElementById(id) : null; };
  var box        = overlay.querySelector(ids.box);
  var videoEl    = $(ids.video);
  var videoWrap  = $(ids.videoWrap) || (videoEl && videoEl.parentNode);
  var titleEl    = $(ids.title);
  var subEl      = $(ids.sub);
  var clientEl   = $(ids.client);
  var tagsEl     = $(ids.tags);
  var closeBtn   = $(ids.close);
  var contactBtn = $(ids.contactBtn);
  var formWrap   = $(ids.formWrap);
  var modalForm  = $(ids.form);
  var formNote   = $(ids.note);
  var scrollHint = $(ids.scrollHint);
  var projectEl  = $(ids.project);
  var firstField = $(ids.firstField);

  if (modalForm) setupPhoneGroup(modalForm);

  function updateScrollHint() {
    if (!scrollHint) return;
    var canScroll = overlay.scrollHeight - overlay.clientHeight > 8;
    var atBottom  = overlay.scrollTop + overlay.clientHeight >= overlay.scrollHeight - 16;
    scrollHint.classList.toggle('show', canScroll && !atBottom);
  }
  function collapseForm() {
    if (formWrap) formWrap.classList.remove('open');
    if (contactBtn) contactBtn.classList.remove('active');
    if (box) box.classList.remove('form-open');
    if (formNote) formNote.textContent = '';
    if (scrollHint) scrollHint.classList.remove('show');
  }

  function open(card) {
    var src    = card.getAttribute('data-video');
    var title  = card.getAttribute('data-title') || '';
    var client = card.getAttribute('data-client') || '';
    var tags   = card.getAttribute('data-tags') || '';
    var sub    = card.getAttribute('data-sub') || '';

    if (titleEl) titleEl.textContent = title;
    if (subEl) subEl.textContent = sub;
    if (clientEl) clientEl.textContent = client;
    if (tagsEl) {
      tagsEl.innerHTML = '';
      tags.split('|').forEach(function (t) {
        t = t.trim();
        if (!t) return;
        var pill = document.createElement('span');
        pill.className = ids.tagClass;
        pill.textContent = t;
        tagsEl.appendChild(pill);
      });
    }
    if (videoEl) {
      if (src) {
        if (videoWrap) { videoWrap.classList.remove('is-hidden'); videoWrap.style.display = ''; }
        videoEl.src = src;
        videoEl.play().catch(function () {});
      } else {
        if (videoWrap) { videoWrap.classList.add('is-hidden'); videoWrap.style.display = 'none'; }
        videoEl.removeAttribute('src');
      }
    }
    collapseForm();
    overlay.classList.add('open');
    overlay.scrollTop = 0;
    document.body.style.overflow = 'hidden';
    setTimeout(updateScrollHint, 100);
  }
  function close() {
    overlay.classList.remove('open');
    document.body.style.overflow = '';
    if (videoEl) { videoEl.pause(); videoEl.removeAttribute('src'); }
    collapseForm();
  }

  if (contactBtn && formWrap) {
    contactBtn.addEventListener('click', function () {
      var isOpen = formWrap.classList.toggle('open');
      contactBtn.classList.toggle('active', isOpen);
      if (box) box.classList.toggle('form-open', isOpen);
      if (isOpen) {
        setTimeout(function () {
          formWrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          if (firstField) firstField.focus();
          updateScrollHint();
        }, 480);
      } else { updateScrollHint(); }
    });
  }

  // Client-side validation only — the form POSTs to WordPress.
  if (modalForm) {
    modalForm.addEventListener('submit', function (e) {
      if (projectEl && titleEl) projectEl.value = titleEl.textContent || '';
      var required = ['name','email','phone','company'];
      for (var i = 0; i < required.length; i++) {
        var f = modalForm.elements[required[i]];
        if (f && !f.value.trim()) {
          e.preventDefault();
          f.focus();
          if (formNote) { formNote.style.color = '#ff6b6b'; formNote.textContent = 'Please fill in all required (*) fields.'; }
          return;
        }
      }
      var phoneErr = validatePhone(modalForm);
      if (phoneErr) {
        e.preventDefault();
        modalForm.elements['phone'].focus();
        if (formNote) { formNote.style.color = '#ff6b6b'; formNote.textContent = phoneErr; }
      }
    });
  }

  overlay.addEventListener('scroll', updateScrollHint, { passive: true });
  if (scrollHint) scrollHint.addEventListener('click', function () { overlay.scrollBy({ top: overlay.clientHeight * 0.7, behavior: 'smooth' }); });
  if (closeBtn) closeBtn.addEventListener('click', close);
  overlay.addEventListener('click', function (e) { if (e.target === overlay) close(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });

  return { open: open, close: close };
}

(function () {
  var homeModal = guruModal({
    overlay: 'hwVmodal', box: '.hw-vmodal-box', video: 'hwVmodalVideo', videoWrap: 'hwVmodalVideoWrap',
    title: 'hwVmodalTitle', sub: 'hwVmodalSub', close: 'hwVmodalClose', contactBtn: 'hwContactBtn',
    formWrap: 'hwFormWrap', form: 'hwContactForm', note: 'hwfNote', scrollHint: 'hwScrollHint',
    project: 'hwf-project', firstField: 'hwf-name', tagClass: ''
  });
  var workModal = guruModal({
    overlay: 'vmodal', box: '.vmodal-box', video: 'vmodalVideo', videoWrap: 'vmodalVideoWrap',
    title: 'vmodalTitle', client: 'vmodalClient', tags: 'vmodalTags', close: 'vmodalClose', contactBtn: 'vmodalContactBtn',
    formWrap: 'vmodalFormWrap', form: 'vmodalForm', note: 'vmfNote', scrollHint: 'vmodalScrollHint',
    project: 'vmf-project', firstField: 'vmf-name', tagClass: 'vmodal-tag'
  });
  var modal = workModal || homeModal;
  if (!modal) return;

  // Showcase cards: play the film in the lightbox, or open the project's own page.
  document.querySelectorAll('.hw-video-card, .hw-info-card, .prj-card').forEach(function (card) {
    var url = card.getAttribute('data-url');
    card.style.cursor = 'pointer';
    card.addEventListener('click', function (e) {
      e.preventDefault();
      if (!card.getAttribute('data-video') && url) { window.location.href = url; return; }
      modal.open(card);
    });
  });
})();

/* ── Work archive: category filter + project count ── */
(function () {
  var grid  = document.getElementById('projectGrid');
  if (!grid) return;
  var btns  = document.querySelectorAll('.filter-btn');
  var cards = grid.querySelectorAll('.prj-card');
  var count = document.getElementById('projectCount');
  var empty = document.getElementById('emptyState');

  function updateCount(n) {
    if (count) count.textContent = n + ' Project' + (n !== 1 ? 's' : '');
  }

  function filter(cat) {
    var visible = 0;
    cards.forEach(function (card) {
      var cats = (card.getAttribute('data-cats') || '').split(' ');
      var show = cat === 'all' || cats.indexOf(cat) !== -1;
      if (show) {
        card.style.display = '';
        card.style.animationDelay = (0.38 + visible * 0.06) + 's';
        card.style.animation = 'none';
        void card.offsetWidth; /* reflow so the entrance animation replays */
        card.style.animation = '';
        visible++;
      } else {
        card.style.display = 'none';
      }
    });
    if (empty) empty.classList.toggle('show', visible === 0);
    updateCount(visible);
  }

  btns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      btns.forEach(function (b) { b.classList.remove('active'); });
      btn.classList.add('active');
      filter(btn.getAttribute('data-filter'));
    });
  });

  updateCount(cards.length);
})();

/* ── Smooth scroll for same-page anchors ── */
document.querySelectorAll('a[href^="#"]').forEach(function(a){
  a.addEventListener('click', function(e){
    var href = a.getAttribute('href');
    if (href.length < 2) return;
    var el = document.getElementById(decodeURIComponent(href.slice(1)));
    if (!el) return;
    e.preventDefault();
    window.scrollTo({ top: el.getBoundingClientRect().top + window.scrollY - 20, behavior: 'smooth' });
  });
});

/* ── Mobile menu toggle ── */
(function() {
  var toggle = document.getElementById('gnToggle');
  var menu   = document.getElementById('gnMobileMenu');
  if (!toggle || !menu) return;

  function openMenu() {
    menu.classList.add('open');
    toggle.classList.add('open');
    toggle.setAttribute('aria-expanded', 'true');
  }
  function closeMenu() {
    menu.classList.remove('open');
    toggle.classList.remove('open');
    toggle.setAttribute('aria-expanded', 'false');
  }

  toggle.addEventListener('click', function() {
    menu.classList.contains('open') ? closeMenu() : openMenu();
  });
  menu.querySelectorAll('[data-close]').forEach(function(el) {
    el.addEventListener('click', closeMenu);
  });
  document.addEventListener('click', function(e) {
    if (!menu.contains(e.target) && !toggle.contains(e.target)) closeMenu();
  });
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeMenu();
  });
})();

/* ── Count-up animation for stat numbers ── */
(function () {
  var els = document.querySelectorAll('.abt-stat-num[data-count]');
  if (!els.length || !('IntersectionObserver' in window)) return;

  function easeOutQuart(t) { return 1 - Math.pow(1 - t, 4); }

  function animateCount(el) {
    var target  = parseInt(el.getAttribute('data-count'), 10);
    var suffix  = el.getAttribute('data-suffix') || '';
    var dur     = 1800;          // ms
    var start   = null;

    function step(ts) {
      if (!start) start = ts;
      var prog = Math.min((ts - start) / dur, 1);
      var val  = Math.round(easeOutQuart(prog) * target);
      el.textContent = val + (prog === 1 ? suffix : '');
      if (prog < 1) requestAnimationFrame(step);
    }

    requestAnimationFrame(step);
  }

  var observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        animateCount(entry.target);
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.4 });

  els.forEach(function (el) { observer.observe(el); });
})();

/* ── Hide nav on scroll down, reveal on scroll up ── */
(function () {
  var nav  = document.querySelector('.gn-nav');
  var menu = document.querySelector('.gn-mobile-menu');
  if (!nav) return;
  var lastY = window.scrollY || 0;
  var ticking = false;
  function apply() {
    var y = window.scrollY || 0;
    var menuOpen = menu && menu.classList.contains('open');
    if (menuOpen || y <= 80) {
      nav.classList.remove('nav-hidden');          // always visible near top / when menu open
    } else if (y > lastY + 6) {
      nav.classList.add('nav-hidden');             // scrolling down
    } else if (y < lastY - 6) {
      nav.classList.remove('nav-hidden');          // scrolling up
    }
    lastY = y;
    ticking = false;
  }
  window.addEventListener('scroll', function () {
    if (!ticking) { ticking = true; requestAnimationFrame(apply); }
  }, { passive: true });
})();

/* ── Homepage: Solutions accordion ── */
(function () {
  var acc = document.getElementById('svcAccordion');
  if (!acc) return;
  var items = acc.querySelectorAll('.svc-acc-item');
  items.forEach(function (item) {
    var head = item.querySelector('.svc-acc-head');
    head.addEventListener('click', function () {
      var isOpen = item.classList.contains('open');
      items.forEach(function (i) { i.classList.remove('open'); });   // single-open
      if (!isOpen) item.classList.add('open');
    });
  });
})();

/* ── Solutions page: panel switching, phone mockups, carousels ── */
(function () {
  var main = document.getElementById('srvMain');
  if (!main) return;

  var navItems = document.querySelectorAll('.srv-nav-item');
  var panels   = document.querySelectorAll('.srv-panel');
  var nextBtns = document.querySelectorAll('.srv-panel-next');
  var carousels = [];

  // Carousels — one instance per .srv-carousel-outer, no shared ids.
  document.querySelectorAll('.srv-carousel-outer').forEach(function (outer) {
    var track  = outer.querySelector('.srv-carousel-track');
    var slides = outer.querySelectorAll('.srv-carousel-slide');
    var dots   = outer.querySelectorAll('.srv-carousel-dot');
    var prev   = outer.querySelector('.srv-carousel-prev');
    var next   = outer.querySelector('.srv-carousel-next');
    var count  = outer.querySelector('.srv-carousel-count');
    var cur    = 0;
    var total  = slides.length;
    if (!track || !total) return;

    function go(n) {
      cur = (n + total) % total;
      track.style.transform = 'translateX(-' + (cur * 100) + '%)';
      if (count) count.textContent = (cur + 1) + ' / ' + total;
      dots.forEach(function (d, i) { d.classList.toggle('active', i === cur); });
      slides.forEach(function (s, i) {
        var v = s.querySelector('video');
        if (!v) return;
        if (i === cur) { v.play().catch(function () {}); }
        else { v.pause(); v.currentTime = 0; }
      });
    }
    function pause() {
      slides.forEach(function (s) { var v = s.querySelector('video'); if (v) v.pause(); });
    }

    if (prev) prev.addEventListener('click', function () { go(cur - 1); });
    if (next) next.addEventListener('click', function () { go(cur + 1); });
    dots.forEach(function (d) { d.addEventListener('click', function () { go(+d.getAttribute('data-idx')); }); });

    var touchX = 0;
    track.addEventListener('touchstart', function (e) { touchX = e.touches[0].clientX; }, { passive: true });
    track.addEventListener('touchend', function (e) {
      var dx = touchX - e.changedTouches[0].clientX;
      if (Math.abs(dx) > 40) go(dx > 0 ? cur + 1 : cur - 1);
    });

    carousels.push({ el: outer, play: function () { go(cur); }, pause: pause });
  });

  function showPanel(slug) {
    var target = document.getElementById('panel-' + slug);
    if (!target) return false;
    var navItem = document.querySelector('.srv-nav-item[data-panel="' + slug + '"]');

    panels.forEach(function (p) {
      p.classList.remove('active');
      p.querySelectorAll('video').forEach(function (v) { v.pause(); });
    });
    navItems.forEach(function (n) { n.classList.remove('active'); });

    target.classList.add('active');
    // Autoplaying videos (single-video block) resume; carousels play their current slide.
    target.querySelectorAll('.srv-video-wrap video, video[autoplay]').forEach(function (v) { v.play().catch(function () {}); });
    carousels.forEach(function (c) { if (target.contains(c.el)) c.play(); else c.pause(); });

    if (navItem) {
      navItem.classList.add('active');
      navItem.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    }
    if (window.innerWidth < 980) {
      window.scrollTo({ top: main.offsetTop - 120, behavior: 'smooth' });
    } else {
      main.scrollTop = 0;
    }
    if (history.replaceState) history.replaceState(null, '', '#' + slug);
    return true;
  }

  navItems.forEach(function (item) {
    item.addEventListener('click', function (e) {
      e.preventDefault();
      showPanel(item.getAttribute('data-panel'));
    });
  });
  nextBtns.forEach(function (btn) {
    btn.addEventListener('click', function () { showPanel(btn.getAttribute('data-next')); });
  });

  // Phone mockups: drag / touch to scroll the screenshot.
  document.querySelectorAll('.srv-phone-screen').forEach(function (screen) {
    var isDown = false, startY = 0, scrollTop = 0;
    screen.addEventListener('mousedown', function (e) {
      isDown = true; screen.style.cursor = 'grabbing';
      startY = e.pageY - screen.offsetTop;
      scrollTop = screen.scrollTop;
    });
    screen.addEventListener('mouseleave', function () { isDown = false; screen.style.cursor = 'grab'; });
    screen.addEventListener('mouseup',    function () { isDown = false; screen.style.cursor = 'grab'; });
    screen.addEventListener('mousemove',  function (e) {
      if (!isDown) return;
      e.preventDefault();
      var y = e.pageY - screen.offsetTop;
      screen.scrollTop = scrollTop - (y - startY) * 1.4;
    });
    var tStartY = 0;
    screen.addEventListener('touchstart', function (e) { tStartY = e.touches[0].clientY; }, { passive: true });
    screen.addEventListener('touchmove', function (e) {
      var dy = tStartY - e.touches[0].clientY;
      screen.scrollTop += dy * 1.2;
      tStartY = e.touches[0].clientY;
    }, { passive: true });
  });

  // Open the panel named in the URL hash (homepage accordion links here), else the first.
  var initial = window.location.hash.replace('#', '');
  if (!initial || !showPanel(initial)) {
    var first = navItems.length ? navItems[0].getAttribute('data-panel') : '';
    if (first) showPanel(first);
  }
  // Also react to hash changes from links elsewhere on the page.
  window.addEventListener('hashchange', function () {
    var slug = window.location.hash.replace('#', '');
    if (slug) showPanel(slug);
  });
})();

/* ── Blog: category filter ── */
(function () {
  var btns  = document.querySelectorAll('.blg-filter-btn');
  if (!btns.length) return;
  var cards = document.querySelectorAll('.art-card');

  var empty = document.getElementById('blgEmpty');

  btns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      var active = btn.getAttribute('data-cat');
      var visible = 0;
      btns.forEach(function (b) { b.classList.remove('active'); });
      btn.classList.add('active');
      cards.forEach(function (card) {
        var cats = (card.getAttribute('data-cat') || '').split(' ');
        var show = active === 'all' || cats.indexOf(active) !== -1;
        card.classList.toggle('hidden', !show);
        card.style.display = show ? '' : 'none';
        if (show) visible++;
      });
      if (empty) empty.classList.toggle('show', visible === 0);
    });
  });
})();

/* ── Footer contact form: client-side validation; POSTs to WordPress ── */
(function () {
  var form = document.getElementById('contactForm');
  var note = document.getElementById('cfNote');
  if (!form) return;
  setupPhoneGroup(form);
  form.addEventListener('submit', function (e) {
    var required = ['name','email','phone','company'];
    for (var i = 0; i < required.length; i++) {
      var f = form.elements[required[i]];
      if (f && !f.value.trim()) {
        e.preventDefault();
        f.focus();
        note.style.color = '#c0392b';
        note.textContent = 'Please fill in all required (*) fields.';
        return;
      }
    }
    var phoneErr = validatePhone(form);
    if (phoneErr) {
      e.preventDefault();
      form.elements['phone'].focus();
      note.style.color = '#c0392b';
      note.textContent = phoneErr;
    }
  });
})();

/* ── Per-solution contact forms: same validation ── */
document.querySelectorAll('.spf-form').forEach(function (form) {
  var note = form.querySelector('.spf-note');
  setupPhoneGroup(form);
  form.addEventListener('submit', function (e) {
    var required = ['name','email','phone','company'];
    for (var i = 0; i < required.length; i++) {
      var f = form.elements[required[i]];
      if (f && !f.value.trim()) {
        e.preventDefault();
        f.focus();
        if (note) { note.style.color = '#c0392b'; note.textContent = 'Please fill in all required (*) fields.'; }
        return;
      }
    }
    var phoneErr = validatePhone(form);
    if (phoneErr) {
      e.preventDefault();
      form.elements['phone'].focus();
      if (note) { note.style.color = '#c0392b'; note.textContent = phoneErr; }
    }
  });
});

/* ── Phone country-code helpers (shared) ── */
function setupPhoneGroup(form) {
  var cc = form.querySelector('.cf-cc, .vmf-cc, .hwf-cc, .spf-cc');
  var phone = form.querySelector('.cf-phone, .vmf-phone, .hwf-phone, .spf-phone');
  if (!cc || !phone) return;
  function apply() {
    var len = cc.options[cc.selectedIndex].getAttribute('data-len');
    if (len) { phone.setAttribute('maxlength', len); }
    else { phone.removeAttribute('maxlength'); }
  }
  cc.addEventListener('change', apply);
  phone.addEventListener('input', function () { phone.value = phone.value.replace(/[^0-9]/g, ''); });
  apply();
}
function validatePhone(form) {
  var cc = form.querySelector('.cf-cc, .vmf-cc, .hwf-cc, .spf-cc');
  var phone = form.querySelector('.cf-phone, .vmf-phone, .hwf-phone, .spf-phone');
  if (!cc || !phone) return '';
  var len = cc.options[cc.selectedIndex].getAttribute('data-len');
  if (len && phone.value.trim().length !== parseInt(len, 10)) {
    return 'For ' + cc.value + ', the contact number must be ' + len + ' digits.';
  }
  return '';
}
