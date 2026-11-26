/* ==========================================================================
   NMC Clinic — shared behaviour
   Replaces what the dc-runtime used to do in renderVals(): the sticky header,
   the mobile menu, the filter tabs and the form success states.
   No framework, no build step.
   ========================================================================== */
(function () {
  'use strict';

  /* ---------- header: solid on scroll, mobile menu ---------------------- */
  var header = document.querySelector('.site-header');

  if (header) {
    var toggle = header.querySelector('.nav-toggle');

    // A 24px sentinel at the top of the page: once it leaves view, the header goes solid.
    // IntersectionObserver instead of a scroll listener, so nothing runs per scroll frame.
    var scrolled = false;
    var syncSolid = function () {
      header.classList.toggle('is-solid', scrolled || header.classList.contains('is-open'));
    };

    if ('IntersectionObserver' in window) {
      var sentinel = document.createElement('div');
      sentinel.setAttribute('aria-hidden', 'true');
      sentinel.style.cssText = 'position:absolute;top:0;left:0;width:1px;height:24px;pointer-events:none;';
      document.body.prepend(sentinel);
      new IntersectionObserver(function (entries) {
        scrolled = !entries[0].isIntersecting;
        syncSolid();
      }).observe(sentinel);
    } else {
      scrolled = true;
    }
    syncSolid();

    // Labels come from the CMS (Site-wide > Header) via data attributes.
    var openLabel = (toggle && toggle.getAttribute('data-label-open')) || 'Open menu';
    var closeLabel = (toggle && toggle.getAttribute('data-label-close')) || 'Close menu';

    if (toggle) {
      toggle.addEventListener('click', function () {
        var open = header.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? closeLabel : openLabel);
        syncSolid();
      });

      // Close the panel after tapping any link inside it.
      header.querySelectorAll('.nav-mobile a').forEach(function (link) {
        link.addEventListener('click', function () {
          header.classList.remove('is-open');
          toggle.setAttribute('aria-expanded', 'false');
          toggle.setAttribute('aria-label', openLabel);
          syncSolid();
        });
      });
    }

    // The menu is desktop-irrelevant; drop it when the breakpoint is crossed.
    var mq = window.matchMedia('(min-width: 1024px)');
    var onBreak = function (e) {
      if (e.matches && header.classList.contains('is-open')) {
        header.classList.remove('is-open');
        if (toggle) {
          toggle.setAttribute('aria-expanded', 'false');
          toggle.setAttribute('aria-label', openLabel);
        }
        syncSolid();
      }
    };
    if (mq.addEventListener) mq.addEventListener('change', onBreak);
    else if (mq.addListener) mq.addListener(onBreak);
  }

  /* ---------- filter tabs ----------------------------------------------- */
  /* Markup contract:
       <div data-filter-group="doctors">
         <button data-filter="All">…</button>
         <button data-filter="Nephrology">…</button>
       </div>
       <div data-filter-target="doctors">
         <article data-category="Nephrology">…</article>
       </div>
     Filtering is presentation only — every record is already in the DOM,
     exactly as the dc version did with filteredDoctors.                    */
  document.querySelectorAll('[data-filter-group]').forEach(function (group) {
    var name = group.getAttribute('data-filter-group');
    var target = document.querySelector('[data-filter-target="' + name + '"]');
    if (!target) return;

    var tabs = group.querySelectorAll('[data-filter]');
    var items = target.querySelectorAll('[data-category]');
    var empty = document.querySelector('[data-filter-empty="' + name + '"]');

    var apply = function (value) {
      var shown = 0;
      items.forEach(function (item) {
        var match = value === 'All' || item.getAttribute('data-category') === value;
        item.hidden = !match;
        if (match) shown++;
      });
      if (empty) empty.hidden = shown !== 0;
    };

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        tabs.forEach(function (t) {
          var on = t === tab;
          t.classList.toggle('is-active', on);
          t.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        apply(tab.getAttribute('data-filter'));
      });
    });
  });

  /* ---------- forms ------------------------------------------------------ */
  /* Markup contract: <form data-success="appointment-ok" action="submit-….php">
     The form posts via fetch; the named panel is revealed with its own
     (server-rendered, editable) success text, or the server's error message.
     A form without an action only reveals the panel.                        */
  document.querySelectorAll('form[data-success]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      var action = form.getAttribute('action');
      var panel = document.getElementById(form.getAttribute('data-success'));
      if (!panel) return;

      // The panel's own text is the editable success message; errors replace it until the next success.
      var textNode = Array.prototype.find.call(panel.childNodes, function (node) {
        return node.nodeType === Node.TEXT_NODE && node.textContent.trim() !== '';
      });
      if (textNode && !panel.hasAttribute('data-ok-text')) panel.setAttribute('data-ok-text', textNode.textContent);

      function showPanel(error) {
        if (textNode) textNode.textContent = error ? ' ' + error : panel.getAttribute('data-ok-text');
        // The dc source kept the form on screen and revealed a message below the
        // submit button, so the form is deliberately NOT hidden here.
        panel.hidden = false;
        panel.setAttribute('tabindex', '-1');
        panel.focus({ preventScroll: true });
        panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }

      if (!action || !window.fetch) {
        showPanel();
        return;
      }

      var submit = form.querySelector('[type="submit"]');
      if (submit) submit.disabled = true;

      fetch(action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'Accept': 'application/json' }
      })
        .then(function (response) {
          return response.json().catch(function () {
            return { ok: false, message: 'Unable to submit right now. Please call the clinic directly.' };
          });
        })
        .then(function (data) {
          showPanel(data.ok ? '' : data.message || 'Unable to submit right now. Please call the clinic directly.');
          if (data.ok) form.reset();
        })
        .catch(function () {
          showPanel('Unable to submit right now. Please call the clinic directly.');
        })
        .finally(function () {
          if (submit) submit.disabled = false;
        });
    });
  });

  /* ---------- scroll reveal ---------------------------------------------- */
  /* Children of [data-reveal] fade up once, staggered. Skipped entirely when
     the visitor prefers reduced motion, so content is never left hidden. */
  var motionOk = window.matchMedia && !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (motionOk && 'IntersectionObserver' in window) {
    var revealer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-in');
        revealer.unobserve(entry.target);
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 });

    document.querySelectorAll('[data-reveal]').forEach(function (group) {
      Array.prototype.forEach.call(group.children, function (child, i) {
        child.classList.add('reveal');
        child.style.setProperty('--i', String(i));
        revealer.observe(child);
      });
    });
  }

  /* ---------- open-now status (clinic local time) ----------------------- */
  /* Defaults mirror the published hours; the page's data-hours (CMS Settings) replaces them. */
  var DEFAULT_HOURS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'].map(function (day) {
    return { day: day, opens_at: '08:00', closes_at: '20:00', is_closed: false };
  }).concat([{ day: 'Saturday', opens_at: '09:00', closes_at: '14:00', is_closed: false }]);

  function clockLabel(hhmm) {
    var parts = hhmm.split(':');
    var h = parseInt(parts[0], 10);
    var suffix = h >= 12 ? 'PM' : 'AM';
    var h12 = h % 12 || 12;
    return h12 + (parts[1] && parts[1] !== '00' ? ':' + parts[1] : '') + ' ' + suffix;
  }

  function renderOpenStatus(hours) {
    var text = document.querySelector('[data-open-status]');
    var dot = document.querySelector('[data-open-dot]');
    if (!text || !window.Intl) return;

    var now;
    try {
      now = new Intl.DateTimeFormat('en-US', {
        timeZone: 'Asia/Kathmandu', weekday: 'long', hour: '2-digit', minute: '2-digit', hourCycle: 'h23'
      }).formatToParts(new Date()).reduce(function (acc, part) { acc[part.type] = part.value; return acc; }, {});
    } catch (e) {
      return;
    }

    var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    var byDay = {};
    hours.forEach(function (row) { byDay[row.day] = row; });
    var isOpenRow = function (row) { return row && !row.is_closed && row.opens_at && row.closes_at; };
    var nowTime = now.hour + ':' + now.minute;
    var today = byDay[now.weekday];
    var open = isOpenRow(today) && nowTime >= today.opens_at && nowTime < today.closes_at;

    if (open) {
      text.textContent = 'Open now, until ' + clockLabel(today.closes_at);
    } else if (isOpenRow(today) && nowTime < today.opens_at) {
      text.textContent = 'Closed now, opens today at ' + clockLabel(today.opens_at);
    } else {
      var start = days.indexOf(now.weekday);
      var next = null;
      for (var i = 1; i <= 7 && !next; i++) {
        var row = byDay[days[(start + i) % 7]];
        if (isOpenRow(row)) next = row;
      }
      text.textContent = next ? 'Closed now, opens ' + next.day + ' at ' + clockLabel(next.opens_at) : 'Closed now';
    }
    if (dot) dot.classList.toggle('is-open', open);
  }

  (function () {
    var el = document.querySelector('[data-open-status]');
    var hours = null;
    try { hours = JSON.parse(el && el.getAttribute('data-hours') || 'null'); } catch (e) { hours = null; }
    // Only trust CMS hours that carry real times; label-only rows keep the defaults.
    var usable = Array.isArray(hours) && hours.some(function (row) { return row && row.opens_at && row.closes_at; });
    renderOpenStatus(usable ? hours : DEFAULT_HOURS);
  })();

  /* ---------- current year ---------------------------------------------- */
  document.querySelectorAll('[data-year]').forEach(function (el) {
    el.textContent = String(new Date().getFullYear());
  });

  /* ---------- branch picker (home "Visit us", contact page) ------------- */
  /* Markup contract, inside one [data-branch-picker] container:
       [data-branch-list]        holds .branch-item > button.branch-select[data-branch-*]
       iframe[data-branch-map]   the map that follows the selected branch
       [data-branch-directions]  directions link, [data-branch-caption] live caption
     [data-branch-field="name|address"] anywhere on the page mirrors the selection.
     The page renders the branch items; the picker's data-label-* / data-*-prefix
     attributes carry the editable button, caption and map-title text. */
  function branchQuery(data) {
    return data.lat && data.lng ? data.lat + ',' + data.lng : data.address;
  }

  function branchMapUrl(data) {
    return 'https://www.google.com/maps?q=' + encodeURIComponent(branchQuery(data)) +
      '&z=' + (parseInt(data.zoom, 10) || 15) + '&output=embed';
  }

  function branchDirectionsUrl(data) {
    return 'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(branchQuery(data));
  }

  function branchData(button) {
    return {
      name: button.getAttribute('data-branch-name') || '',
      address: button.getAttribute('data-branch-address') || '',
      lat: button.getAttribute('data-branch-lat') || '',
      lng: button.getAttribute('data-branch-lng') || '',
      zoom: button.getAttribute('data-branch-zoom') || '15',
      phone: button.getAttribute('data-branch-phone') || '',
      phoneHref: button.getAttribute('data-branch-phone-href') || ''
    };
  }

  function selectBranch(picker, button) {
    var data = branchData(button);
    var labels = {
      show: picker.getAttribute('data-label-show') || 'Show on map',
      showing: picker.getAttribute('data-label-showing') || 'Showing on map',
      caption: picker.getAttribute('data-caption-prefix') || 'Showing',
      title: picker.getAttribute('data-title-prefix') || 'Map of NMC'
    };

    picker.querySelectorAll('.branch-select').forEach(function (item) {
      var on = item === button;
      item.setAttribute('aria-pressed', on ? 'true' : 'false');
      item.closest('.branch-item').classList.toggle('is-active', on);
      var cta = item.querySelector('[data-branch-cta]');
      if (cta) cta.textContent = on ? labels.showing : labels.show;
    });

    var map = picker.querySelector('[data-branch-map]');
    var url = branchMapUrl(data);
    if (map && map.getAttribute('src') !== url) {
      map.setAttribute('src', url);
      map.setAttribute('title', labels.title + ' ' + data.name);
    }
    var directions = picker.querySelector('[data-branch-directions]');
    if (directions) directions.href = branchDirectionsUrl(data);
    var caption = picker.querySelector('[data-branch-caption]');
    if (caption) caption.textContent = labels.caption + ' ' + data.name;

    document.querySelectorAll('[data-branch-field]').forEach(function (field) {
      var key = field.getAttribute('data-branch-field');
      // Remember the page's original value, so a branch without a phone
      // falls back to the main number instead of keeping the last branch's.
      if (!field.hasAttribute('data-default-text')) {
        field.setAttribute('data-default-text', field.textContent);
        if (field.tagName === 'A') field.setAttribute('data-default-href', field.getAttribute('href') || '');
      }
      var text = data[key] || field.getAttribute('data-default-text');
      field.textContent = text;
      if (key === 'phone' && field.tagName === 'A') {
        field.href = data.phone && data.phoneHref ? data.phoneHref : field.getAttribute('data-default-href');
      }
    });
  }

  var branchPickers = document.querySelectorAll('[data-branch-picker]');

  branchPickers.forEach(function (picker) {
    var list = picker.querySelector('[data-branch-list]');
    if (!list) return;
    list.addEventListener('click', function (event) {
      var button = event.target.closest('.branch-select');
      if (button && list.contains(button)) selectBranch(picker, button);
    });
  });
})();
