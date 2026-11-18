/* Guru theme — front-end behaviour. Mirrors the inline scripts of the static site. */

/* ── Image slider for showcase items without a film ──
 * Swipe on phones (native scroll-snap), arrows / keys / trackpad on desktop.
 * Used in both lightboxes (filled from the card's data-gallery) and on the
 * item's own page (slides already in the markup). */
function guruGallery(root) {
  if (!root) return null;
  var track = root.querySelector('.gg-track');
  var prev  = root.querySelector('.gg-prev');
  var next  = root.querySelector('.gg-next');
  var count = root.querySelector('.gg-count');
  var dots  = root.querySelector('.gg-dots');
  var cur = 0;

  function slides() { return track.querySelectorAll('.gg-slide'); }

  function render() {
    var n = slides().length;
    root.setAttribute('data-count', n);
    root.classList.toggle('is-single', n < 2);
    dots.innerHTML = '';
    for (var i = 0; i < n; i++) {
      var d = document.createElement('button');
      d.type = 'button';
      d.className = 'gg-dot';
      d.setAttribute('aria-label', 'Image ' + (i + 1));
      d.setAttribute('data-idx', i);
      dots.appendChild(d);
    }
    cur = 0;
    track.scrollLeft = 0;
    update();
  }

  // Only the film on the current slide may play (v1.3.1).
  function pauseOthers() {
    slides().forEach(function (sl, i) {
      if (i === cur) return;
      var v = sl.querySelector('video');
      if (v && !v.paused) v.pause();
    });
  }

  function update() {
    pauseOthers();
    var n = slides().length;
    if (count) count.textContent = n > 1 ? (cur + 1) + ' / ' + n : '';
    if (prev) prev.disabled = cur <= 0;
    if (next) next.disabled = cur >= n - 1;
    dots.querySelectorAll('.gg-dot').forEach(function (d, i) { d.classList.toggle('active', i === cur); });
  }

  function go(i, instant) {
    var n = slides().length;
    if (!n) return;
    cur = Math.max(0, Math.min(n - 1, i));
    track.scrollTo({ left: cur * track.clientWidth, behavior: instant ? 'auto' : 'smooth' });
    update();
  }

  var raf = 0;
  track.addEventListener('scroll', function () {
    cancelAnimationFrame(raf);
    raf = requestAnimationFrame(function () {
      var w = track.clientWidth || 1;
      var i = Math.round(track.scrollLeft / w);
      if (i !== cur) { cur = i; update(); }
    });
  }, { passive: true });

  if (prev) prev.addEventListener('click', function (e) { e.stopPropagation(); go(cur - 1); });
  if (next) next.addEventListener('click', function (e) { e.stopPropagation(); go(cur + 1); });
  dots.addEventListener('click', function (e) {
    var d = e.target.closest('.gg-dot');
    if (d) go(+d.getAttribute('data-idx'));
  });
  track.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowLeft') { e.preventDefault(); go(cur - 1); }
    if (e.key === 'ArrowRight') { e.preventDefault(); go(cur + 1); }
  });

  // Desktop: drag with the mouse like on a phone.
  var down = false, startX = 0, startLeft = 0, moved = false;
  track.addEventListener('mousedown', function (e) {
    if (e.button !== 0) return;
    if (e.target.closest && e.target.closest('video')) return; // film controls (seek bar, volume) keep the mouse
    down = true; moved = false; startX = e.clientX; startLeft = track.scrollLeft;
    track.classList.add('is-dragging');
  });
  window.addEventListener('mousemove', function (e) {
    if (!down) return;
    var dx = e.clientX - startX;
    if (Math.abs(dx) > 4) moved = true;
    track.scrollLeft = startLeft - dx;
  });
  window.addEventListener('mouseup', function (e) {
    if (!down) return;
    down = false;
    track.classList.remove('is-dragging');
    var dx = e.clientX - startX;
    var w = track.clientWidth || 1;
    // A short flick is enough to move one picture.
    var target = Math.abs(dx) > w * 0.12 ? cur + (dx < 0 ? 1 : -1) : cur;
    go(target);
  });
  track.addEventListener('click', function (e) { if (moved) { e.preventDefault(); e.stopPropagation(); } }, true);

  // Keep the current picture in place when the window is resized or rotated.
  window.addEventListener('resize', function () { if (slides().length) go(cur, true); });

  render();

  return {
    set: function (items) {
      track.innerHTML = '';
      (items || []).forEach(function (it, i) {
        var slide = document.createElement('div');
        slide.className = 'gg-slide';
        slide.setAttribute('role', 'group');
        slide.setAttribute('aria-roledescription', 'slide');
        slide.setAttribute('aria-label', (i + 1) + ' / ' + items.length);
        if (it.v) {
          // Film slide (v1.3.1): first in the slider when the item has a film and pictures.
          slide.className += ' gg-slide-video';
          var vid = document.createElement('video');
          vid.controls = true;
          vid.playsInline = true;
          vid.setAttribute('playsinline', '');
          vid.preload = 'metadata';
          vid.src = it.v;
          slide.appendChild(vid);
          track.appendChild(slide);
          return;
        }
        var img = document.createElement('img');
        img.src = it.s;
        img.alt = it.a || '';
        if (it.w && it.h) { img.width = it.w; img.height = it.h; }
        img.decoding = 'async';
        img.draggable = false;
        if (i > 1) img.loading = 'lazy';
        slide.appendChild(img);
        track.appendChild(slide);
      });
      root.classList.toggle('is-hidden', !items || !items.length);
      render();
    },
    clear: function () {
      track.querySelectorAll('video').forEach(function (v) { v.pause(); v.removeAttribute('src'); v.load(); });
      track.innerHTML = ''; root.classList.add('is-hidden'); render();
    },
    video: function () { var s = slides()[cur]; return s ? s.querySelector('video') : null; },
    go: function (i) { go(i); },
    step: function (d) { go(cur + d); },
    count: function () { return slides().length; }
  };
}

/* The item's own page: slides are already in the markup. */
(function () {
  var g = guruGallery(document.getElementById('wrkSingleGallery'));
  if (!g) return;
  document.addEventListener('keydown', function (e) {
    var t = e.target;
    if (t && (/^(INPUT|TEXTAREA|SELECT)$/.test(t.tagName) || t.closest('.gg-track'))) return;
    if (e.key === 'ArrowLeft') g.step(-1);
    if (e.key === 'ArrowRight') g.step(1);
  });
})();

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
  var gallery    = guruGallery($(ids.gallery));

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

  var lastFocus = null;

  function open(card) {
    lastFocus = document.activeElement;
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
    var pics = [];
    if (gallery) {
      try { pics = JSON.parse(card.getAttribute('data-gallery') || '[]'); } catch (err) { pics = []; }
    }
    // Film and pictures (v1.3.1): the film becomes the first slide of the slider.
    var mixed = !!(src && gallery && pics.length);

    if (videoEl) {
      if (src && !mixed) {
        if (videoWrap) { videoWrap.classList.remove('is-hidden'); videoWrap.style.display = ''; }
        videoEl.src = src;
        videoEl.play().catch(function () {});
      } else {
        if (videoWrap) { videoWrap.classList.add('is-hidden'); videoWrap.style.display = 'none'; }
        videoEl.removeAttribute('src');
      }
    }
    if (gallery) {
      if (mixed) {
        gallery.set([{ v: src }].concat(pics));
        var first = gallery.video();
        if (first) { var pr = first.play(); if (pr && pr.catch) pr.catch(function () {}); }
      } else {
        gallery.set(src ? [] : pics);
      }
    }
    collapseForm();
    overlay.classList.add('open');
    overlay.scrollTop = 0;
    document.body.style.overflow = 'hidden';
    setTimeout(updateScrollHint, 100);
    // Keyboard users land inside the lightbox (v1.3.0).
    if (closeBtn) setTimeout(function () { closeBtn.focus({ preventScroll: true }); }, 60);
  }
  function close() {
    overlay.classList.remove('open');
    document.body.style.overflow = '';
    if (videoEl) { videoEl.pause(); videoEl.removeAttribute('src'); }
    if (gallery) gallery.clear();
    collapseForm();
    // Back to the card that opened it.
    if (lastFocus && lastFocus.focus) { try { lastFocus.focus({ preventScroll: true }); } catch (err) {} }
    lastFocus = null;
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
        return;
      }
      lockSubmit(modalForm, e);
    });
  }

  overlay.addEventListener('scroll', updateScrollHint, { passive: true });
  if (scrollHint) scrollHint.addEventListener('click', function () { overlay.scrollBy({ top: overlay.clientHeight * 0.7, behavior: 'smooth' }); });
  if (closeBtn) closeBtn.addEventListener('click', close);
  overlay.addEventListener('click', function (e) { if (e.target === overlay) close(); });
  document.addEventListener('keydown', function (e) {
    if (!overlay.classList.contains('open')) return;
    if (e.key === 'Escape') close();
    // Left / right arrows page through the pictures (not while typing in the form).
    var t = e.target;
    if (!gallery || !gallery.count() || (t && (/^(INPUT|TEXTAREA|SELECT)$/.test(t.tagName) || t.closest('.gg-track')))) return;
    if (e.key === 'ArrowLeft') gallery.step(-1);
    if (e.key === 'ArrowRight') gallery.step(1);
  });

  return { open: open, close: close };
}

(function () {
  var homeModal = guruModal({
    overlay: 'hwVmodal', box: '.hw-vmodal-box', video: 'hwVmodalVideo', videoWrap: 'hwVmodalVideoWrap', gallery: 'hwVmodalGallery',
    title: 'hwVmodalTitle', sub: 'hwVmodalSub', close: 'hwVmodalClose', contactBtn: 'hwContactBtn',
    formWrap: 'hwFormWrap', form: 'hwContactForm', note: 'hwfNote', scrollHint: 'hwScrollHint',
    project: 'hwf-project', firstField: 'hwf-name', tagClass: ''
  });
  var workModal = guruModal({
    overlay: 'vmodal', box: '.vmodal-box', video: 'vmodalVideo', videoWrap: 'vmodalVideoWrap', gallery: 'vmodalGallery',
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
      if (!card.getAttribute('data-video') && !card.getAttribute('data-gallery') && url) { window.location.href = url; return; }
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
    // Skip link: move keyboard focus into the main content too, not just the view.
    if (a.classList.contains('skip-link')) el.focus({ preventScroll: true });
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

/* ── Showcase card films: load and play only while on screen ── */
(function () {
  var vids = document.querySelectorAll('video[data-lazy-play]');
  if (!vids.length) return;

  function play(v) {
    if (v.preload !== 'auto') v.preload = 'auto';
    var p = v.play();
    if (p && p.catch) p.catch(function () {});
  }

  if (!('IntersectionObserver' in window)) {
    vids.forEach(play);
    return;
  }

  var observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) play(entry.target);
      else entry.target.pause();
    });
  }, { rootMargin: '200px 0px' });

  vids.forEach(function (v) { observer.observe(v); });
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

/* ── Homepage: Solutions accordion ──
   Mouse: hovering a row opens its intro, one click on the name goes to that
   solution (client request, v1.2.7). Touch: tap opens, "Explore" goes. */
(function () {
  var acc = document.getElementById('svcAccordion');
  if (!acc) return;
  var items = acc.querySelectorAll('.svc-acc-item');
  var mouse = window.matchMedia('(hover: hover) and (pointer: fine)');
  var timer = null;

  function setOpen(item, on) {
    item.classList.toggle('open', on);
    var h = item.querySelector('.svc-acc-head');
    if (h) h.setAttribute('aria-expanded', on ? 'true' : 'false');
  }
  function open(item) {
    items.forEach(function (i) { if (i !== item) setOpen(i, false); });   // single-open
    setOpen(item, true);
  }

  items.forEach(function (item) {
    var head = item.querySelector('.svc-acc-head');
    var link = item.querySelector('.svc-acc-link');

    // Short delay so sweeping the mouse across the list doesn't open every row.
    item.addEventListener('mouseenter', function () {
      if (!mouse.matches) return;
      clearTimeout(timer);
      timer = setTimeout(function () { open(item); }, 90);
    });
    item.addEventListener('mouseleave', function () { clearTimeout(timer); });
    head.addEventListener('focus', function () { if (mouse.matches) open(item); });

    head.addEventListener('click', function () {
      if (mouse.matches && link) {
        window.location.href = link.href;
        return;
      }
      var isOpen = item.classList.contains('open');
      items.forEach(function (i) { setOpen(i, false); });
      if (!isOpen) setOpen(item, true);
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
      return;
    }
    lockSubmit(form, e);
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
      return;
    }
    lockSubmit(form, e);
  });
});

/* ── Block a second click while the form is on its way (v1.3.0) ── */
function lockSubmit(form, e) {
  if (form.getAttribute('data-sending')) { e.preventDefault(); return; }
  form.setAttribute('data-sending', '1');
  var btn = form.querySelector('[type="submit"]');
  if (btn) { btn.setAttribute('aria-disabled', 'true'); btn.style.opacity = '0.6'; }
}
// Coming back with the browser's Back button: allow sending again.
window.addEventListener('pageshow', function () {
  document.querySelectorAll('form[data-sending]').forEach(function (f) {
    f.removeAttribute('data-sending');
    var btn = f.querySelector('[type="submit"]');
    if (btn) { btn.removeAttribute('aria-disabled'); btn.style.opacity = ''; }
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
