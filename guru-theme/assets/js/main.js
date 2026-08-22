/* Guru theme — front-end behaviour. */

(function () {
  var overlay   = document.getElementById('hwVmodal');
  var box       = overlay.querySelector('.hw-vmodal-box');
  var videoEl   = document.getElementById('hwVmodalVideo');
  var videoWrap = document.getElementById('hwVmodalVideoWrap');
  var titleEl   = document.getElementById('hwVmodalTitle');
  var subEl     = document.getElementById('hwVmodalSub');
  var closeBtn  = document.getElementById('hwVmodalClose');
  var contactBtn= document.getElementById('hwContactBtn');
  var formWrap  = document.getElementById('hwFormWrap');
  var modalForm = document.getElementById('hwContactForm');
  var formNote  = document.getElementById('hwfNote');
  var scrollHint= document.getElementById('hwScrollHint');
  if (modalForm) {
    var hwCc = modalForm.querySelector('.hwf-cc');
    var hwPhone = modalForm.querySelector('.hwf-phone');
    if (hwCc && hwPhone) {
      var hwApplyCc = function () {
        var len = hwCc.options[hwCc.selectedIndex].getAttribute('data-len');
        if (len) { hwPhone.setAttribute('maxlength', len); } else { hwPhone.removeAttribute('maxlength'); }
      };
      hwCc.addEventListener('change', hwApplyCc);
      hwPhone.addEventListener('input', function () { hwPhone.value = hwPhone.value.replace(/[^0-9]/g, ''); });
      hwApplyCc();
    }
  }

  function updateScrollHint() {
    if (!scrollHint) return;
    var canScroll = overlay.scrollHeight - overlay.clientHeight > 8;
    var atBottom  = overlay.scrollTop + overlay.clientHeight >= overlay.scrollHeight - 16;
    scrollHint.classList.toggle('show', canScroll && !atBottom);
  }
  function collapseForm() {
    formWrap.classList.remove('open');
    contactBtn.classList.remove('active');
    box.classList.remove('form-open');
    formNote.textContent = '';
    scrollHint.classList.remove('show');
  }

  function openModal(src, title, sub) {
    titleEl.textContent = title;
    subEl.textContent = sub;
    if (src) {
      videoWrap.classList.remove('is-hidden');
      videoEl.src = src;
      videoEl.play();
    } else {
      videoWrap.classList.add('is-hidden');
      videoEl.removeAttribute('src');
    }
    collapseForm();
    overlay.classList.add('open');
    overlay.scrollTop = 0;
    document.body.style.overflow = 'hidden';
    setTimeout(updateScrollHint, 100);
  }
  function closeModal() {
    overlay.classList.remove('open');
    document.body.style.overflow = '';
    videoEl.pause();
    videoEl.removeAttribute('src');
    collapseForm();
  }

  // All work cards open the modal (video + non-video)
  document.querySelectorAll('.hw-video-card, .hw-info-card').forEach(function (card) {
    card.style.cursor = 'pointer';
    card.addEventListener('click', function () {
      openModal(
        card.getAttribute('data-video'),
        card.getAttribute('data-title'),
        card.getAttribute('data-sub')
      );
    });
  });

  // Contact form toggle
  contactBtn.addEventListener('click', function () {
    var isOpen = formWrap.classList.toggle('open');
    contactBtn.classList.toggle('active', isOpen);
    box.classList.toggle('form-open', isOpen);
    if (isOpen) {
      setTimeout(function () {
        formWrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        var first = document.getElementById('hwf-name');
        if (first) first.focus();
        updateScrollHint();
      }, 480);
    } else { updateScrollHint(); }
  });

  // Client-side validation only — the form POSTs to WordPress.
  modalForm.addEventListener('submit', function (e) {
    var projectField = document.getElementById('hwf-project');
    if (projectField && titleEl) projectField.value = titleEl.textContent || '';
    var required = ['name','email','phone','company'];
    for (var i = 0; i < required.length; i++) {
      var f = modalForm.elements[required[i]];
      if (!f.value.trim()) {
        e.preventDefault();
        f.focus();
        formNote.style.color = '#ff6b6b';
        formNote.textContent = 'Please fill in all required (*) fields.';
        return;
      }
    }
  });

  overlay.addEventListener('scroll', updateScrollHint, { passive: true });
  if (scrollHint) scrollHint.addEventListener('click', function () { overlay.scrollBy({ top: overlay.clientHeight * 0.7, behavior: 'smooth' }); });

  closeBtn.addEventListener('click', closeModal);
  overlay.addEventListener('click', function (e) { if (e.target === overlay) closeModal(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });
})();

document.querySelectorAll('a[href^="#"]').forEach(function(a){
  a.addEventListener('click', function(e){
    var href = a.getAttribute('href');
    if (href.length < 2) return;
    var el = document.querySelector(href);
    if (!el) return;
    e.preventDefault();
    window.scrollTo({ top: el.getBoundingClientRect().top + window.scrollY - 20, behavior: 'smooth' });
  });
});

// ── Mobile menu toggle ──
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

  // Close when any menu link is clicked
  menu.querySelectorAll('[data-close]').forEach(function(el) {
    el.addEventListener('click', closeMenu);
  });

  // Close on outside click
  document.addEventListener('click', function(e) {
    if (!menu.contains(e.target) && !toggle.contains(e.target)) closeMenu();
  });

  // Close on Escape
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeMenu();
  });
})();

/* ── Count-up animation for stat numbers ── */
(function () {
  var els = document.querySelectorAll('.abt-stat-num[data-count]');
  if (!els.length) return;

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

/* ── Solutions accordion ── */
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

/* ── Contact form: client-side validation; POSTs to WordPress ── */
(function () {
  var form = document.getElementById('contactForm');
  var note = document.getElementById('cfNote');
  if (!form) return;
  form.addEventListener('submit', function (e) {
    var required = ['name','email','phone','company'];
    for (var i = 0; i < required.length; i++) {
      var f = form.elements[required[i]];
      if (!f.value.trim()) {
        e.preventDefault();
        f.focus();
        note.style.color = '#c0392b';
        note.textContent = 'Please fill in all required (*) fields.';
        return;
      }
    }
  });
})();

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