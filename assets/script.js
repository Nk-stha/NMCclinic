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

    if (toggle) {
      toggle.addEventListener('click', function () {
        var open = header.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
        syncSolid();
      });

      // Close the panel after tapping any link inside it.
      header.querySelectorAll('.nav-mobile a').forEach(function (link) {
        link.addEventListener('click', function () {
          header.classList.remove('is-open');
          toggle.setAttribute('aria-expanded', 'false');
          toggle.setAttribute('aria-label', 'Open menu');
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
          toggle.setAttribute('aria-label', 'Open menu');
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
  /* Markup contract: <form data-success="appointment-ok">
     On submit the named element is revealed; the form itself stays on screen.

     NOTE: this mirrors the current behaviour exactly — the original pages only
     called preventDefault() and flipped a submitted flag. Nothing is sent or
     stored. The plan files record this as an open defect, not a finished
     feature.                                                                */
  document.querySelectorAll('form[data-success]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      var action = form.getAttribute('action');
      var panel = document.getElementById(form.getAttribute('data-success'));
      if (!panel) return;

      function showPanel(message) {
        if (message) {
          var textNode = Array.prototype.find.call(panel.childNodes, function (node) {
            return node.nodeType === Node.TEXT_NODE && node.textContent.trim() !== '';
          });
          if (textNode) textNode.textContent = ' ' + message;
        }
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
          showPanel(data.message);
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
  /* Defaults mirror the published hours; CMS hours replace them when loaded. */
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

  renderOpenStatus(DEFAULT_HOURS);

  /* ---------- current year ---------------------------------------------- */
  document.querySelectorAll('[data-year]').forEach(function (el) {
    el.textContent = String(new Date().getFullYear());
  });

  function endpointUrl(file) {
    var script = document.querySelector('script[src*="assets/script.js"]');
    if (!script) return file;

    var src = script.getAttribute('src') || '';
    var index = src.indexOf('assets/script.js');
    if (index === -1) return file;

    return src.slice(0, index) + file;
  }

  function cmsText(value) {
    return value == null ? '' : String(value);
  }

  function blogReadTime(minutes) {
    return minutes ? minutes + ' min read' : 'Health guide';
  }

  function blogByline(post) {
    var reviewer = cmsText(post.reviewer_name) || 'NMC medical team';
    var reviewed = cmsText(post.reviewed_at || post.published_at);
    return 'Reviewed by ' + reviewer + (reviewed ? ' · Reviewed ' + reviewed : '');
  }

  function arrowSvg() {
    return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>';
  }

  function addBlogImage(container, post, fallback) {
    if (post.image_path) {
      var img = document.createElement('img');
      img.src = post.image_path;
      img.alt = cmsText(post.image_alt || post.title);
      img.loading = 'lazy';
      container.appendChild(img);
      return;
    }

    var label = document.createElement('span');
    label.className = 'ph-label';
    label.textContent = fallback;
    container.appendChild(label);
  }

  function createBlogPostCard(post, featured) {
    var card = document.createElement('a');
    card.className = featured ? 'featured' : 'post-card';
    card.href = post.url || ('blog/' + encodeURIComponent(cmsText(post.slug)));
    card.setAttribute('data-category', cmsText(post.category));

    var photo = document.createElement('div');
    photo.className = featured ? 'featured-photo' : 'post-photo';
    addBlogImage(photo, post, featured ? 'featured article image' : 'article image');
    var tag = document.createElement('span');
    tag.className = 'doc-tag';
    tag.textContent = (featured ? 'Featured · ' : '') + cmsText(post.category);
    photo.appendChild(tag);

    var body = document.createElement('div');
    body.className = featured ? 'featured-body' : 'post-body';

    var meta = document.createElement('div');
    meta.className = 'post-meta';
    meta.textContent = blogReadTime(post.reading_time_minutes);
    var title = document.createElement(featured ? 'h2' : 'h3');
    title.textContent = cmsText(post.title);
    var excerpt = document.createElement('p');
    excerpt.textContent = cmsText(post.excerpt);
    var review = document.createElement('div');
    review.className = 'post-byline';
    review.textContent = blogByline(post);
    var link = document.createElement('span');
    link.className = 'link-cta inline';
    link.innerHTML = 'Read article ' + arrowSvg();

    body.appendChild(meta);
    body.appendChild(title);
    body.appendChild(excerpt);
    body.appendChild(review);
    body.appendChild(link);
    card.appendChild(photo);
    card.appendChild(body);

    return card;
  }

  function calendarSvg() {
    return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#286963" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>';
  }

  function scheduleText(schedules) {
    if (!Array.isArray(schedules) || schedules.length === 0) return '';
    return schedules.map(function (schedule) {
      var time = schedule.start_time && schedule.end_time ? ' ' + schedule.start_time + '-' + schedule.end_time : '';
      var location = schedule.location ? ' · ' + schedule.location : '';
      return schedule.day + time + location;
    }).join(', ');
  }

  function createDoctorCard(doctor, home) {
    var card = document.createElement('article');
    card.className = 'doc-card' + (home ? ' is-home' : '');
    card.setAttribute('data-category', cmsText(doctor.specialty));

    var photo = document.createElement('div');
    photo.className = 'doc-photo';
    if (doctor.photo_path) {
      var img = document.createElement('img');
      img.src = doctor.photo_path;
      img.alt = cmsText(doctor.photo_alt || doctor.full_name);
      img.loading = 'lazy';
      photo.appendChild(img);
    } else {
      var placeholder = document.createElement('span');
      placeholder.className = 'ph-label';
      placeholder.textContent = 'doctor photo';
      photo.appendChild(placeholder);
    }

    var body = document.createElement('div');
    body.className = 'doc-body';
    if (home) {
      var chip = document.createElement('span');
      chip.className = 'doc-chip';
      chip.textContent = cmsText(doctor.specialty);
      body.appendChild(chip);
    } else {
      var tag = document.createElement('span');
      tag.className = 'doc-tag';
      tag.textContent = cmsText(doctor.specialty);
      photo.appendChild(tag);
    }

    var name = document.createElement('h3');
    name.textContent = cmsText(doctor.full_name);
    var hours = document.createElement('div');
    hours.className = 'doc-hours';
    hours.innerHTML = calendarSvg() + ' ';
    hours.appendChild(document.createTextNode(scheduleText(doctor.schedules)));
    var actions = document.createElement('div');
    actions.className = 'doc-actions';
    actions.innerHTML = home
      ? '<a class="doc-view" href="#appointment">Book a visit</a>'
      : '<a class="doc-view-solid" href="home#appointment">Book a visit</a>';

    body.appendChild(name);
    // Qualification and registration lines only when the CMS has them; no placeholder text.
    if (Array.isArray(doctor.qualifications) && doctor.qualifications.length > 0) {
      var qual = document.createElement('p');
      qual.className = 'doc-qual';
      qual.textContent = doctor.qualifications.join(', ');
      body.appendChild(qual);
    }
    if (doctor.registration_number) {
      var reg = document.createElement('p');
      reg.className = 'doc-reg';
      reg.textContent = 'NMC Reg. No. ' + doctor.registration_number;
      body.appendChild(reg);
    }
    if (hours.textContent.trim()) body.appendChild(hours);
    body.appendChild(actions);
    card.appendChild(photo);
    card.appendChild(body);

    return card;
  }

  function createHomeSpecialtyCard(specialty) {
    var card = document.createElement('article');
    card.className = 'card card-hover svc-card';
    card.innerHTML = '<span class="icon-badge"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v18"></path><path d="M5 12h14"></path><circle cx="12" cy="12" r="8"></circle></svg></span>';

    var title = document.createElement('h3');
    title.textContent = cmsText(specialty.title);
    var description = document.createElement('p');
    description.textContent = cmsText(specialty.short_description);
    var link = document.createElement('a');
    link.className = 'link-cta inline';
    link.href = 'specialties#' + encodeURIComponent(cmsText(specialty.slug));
    link.innerHTML = 'Learn more ' + arrowSvg();

    card.appendChild(title);
    card.appendChild(description);
    card.appendChild(link);

    return card;
  }

  function setAnchor(anchor, href, label) {
    if (!anchor || !href) return;
    anchor.href = href;
    if (label) {
      Array.prototype.forEach.call(anchor.childNodes, function (node) {
        if (node.nodeType === Node.TEXT_NODE && node.textContent.trim() !== '') {
          node.textContent = ' ' + label;
        }
      });
      if (anchor.textContent.trim() === '') anchor.textContent = label;
    }
  }

  function replaceFooterContact(settings, hours) {
    document.querySelectorAll('.footer-contact').forEach(function (list) {
      var items = list.children;
      if (items.length > 0 && settings.address) items[0].textContent = settings.address;
      if (items.length > 1 && settings.email) {
        items[1].textContent = settings.email;
        if (items[1].tagName === 'A') items[1].href = settings.email_href;
      }
      if (items.length > 2 && hours.length > 0) {
        items[2].textContent = hours[0].day + ': ' + (hours[0].is_closed ? 'Closed' : (hours[0].label || hours[0].opens_at + '-' + hours[0].closes_at));
      }
      if (items.length > 3 && hours.length > 1) {
        var last = hours[hours.length - 1];
        items[3].textContent = last.day + ': ' + (last.is_closed ? 'Closed' : (last.label || last.opens_at + '-' + last.closes_at));
      }
    });
  }

  function applySiteSettings(data) {
    if (!data || !data.ok || !data.settings) return;

    var settings = data.settings;
    var hours = Array.isArray(data.hours) ? data.hours : [];

    document.querySelectorAll('.header-phone, .footer-phone').forEach(function (anchor) {
      setAnchor(anchor, settings.primary_phone_href, settings.primary_phone);
    });
    document.querySelectorAll('.wa-float, [data-whatsapp]').forEach(function (anchor) {
      if (settings.whatsapp_url) anchor.href = settings.whatsapp_url;
    });
    document.querySelectorAll('[data-phone-href]').forEach(function (anchor) {
      if (settings.primary_phone_href) anchor.href = settings.primary_phone_href;
    });
    document.querySelectorAll('[data-phone]').forEach(function (item) {
      if (settings.primary_phone) item.textContent = settings.primary_phone;
    });
    // Only trust CMS hours that carry real times; label-only rows keep the defaults.
    if (hours.some(function (row) { return row.opens_at && row.closes_at; })) renderOpenStatus(hours);
    // Branch maps are driven by the branch picker, not the single global pin.
    document.querySelectorAll('iframe[title*="location"]:not([data-branch-map])').forEach(function (iframe) {
      if (settings.map_embed_url) iframe.src = settings.map_embed_url;
    });

    replaceFooterContact(settings, hours);

    document.querySelectorAll('.footer-legal a').forEach(function (anchor) {
      var text = anchor.textContent.trim().toLowerCase();
      if (text.indexOf('privacy') !== -1 && settings.privacy_url) anchor.href = settings.privacy_url;
      if ((text.indexOf('terms') !== -1 || text.indexOf('conditions') !== -1) && settings.terms_url) anchor.href = settings.terms_url;
      if (text.indexOf('accessibility') !== -1 && settings.accessibility_url) anchor.href = settings.accessibility_url;
    });
  }

  /* ---------- branch picker (home "Visit us", contact page) ------------- */
  /* Markup contract, inside one [data-branch-picker] container:
       [data-branch-list]        holds .branch-item > button.branch-select[data-branch-*]
       iframe[data-branch-map]   the map that follows the selected branch
       [data-branch-directions]  directions link, [data-branch-caption] live caption
     [data-branch-field="name|address"] anywhere on the page mirrors the selection.
     The built-in items are the fallback; CMS branches replace them when loaded. */
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

    picker.querySelectorAll('.branch-select').forEach(function (item) {
      var on = item === button;
      item.setAttribute('aria-pressed', on ? 'true' : 'false');
      item.closest('.branch-item').classList.toggle('is-active', on);
      var cta = item.querySelector('[data-branch-cta]');
      if (cta) cta.textContent = on ? 'Showing on map' : 'Show on map';
    });

    var map = picker.querySelector('[data-branch-map]');
    var url = branchMapUrl(data);
    if (map && map.getAttribute('src') !== url) {
      map.setAttribute('src', url);
      map.setAttribute('title', 'Map of NMC ' + data.name);
    }
    var directions = picker.querySelector('[data-branch-directions]');
    if (directions) directions.href = branchDirectionsUrl(data);
    var caption = picker.querySelector('[data-branch-caption]');
    if (caption) caption.textContent = 'Showing ' + data.name;

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

  function createBranchItem(branch) {
    var item = document.createElement('div');
    item.className = 'branch-item';

    var button = document.createElement('button');
    button.type = 'button';
    button.className = 'branch-select';
    button.setAttribute('aria-pressed', 'false');
    button.setAttribute('data-branch-name', cmsText(branch.name));
    button.setAttribute('data-branch-address', cmsText(branch.address));
    button.setAttribute('data-branch-lat', cmsText(branch.lat));
    button.setAttribute('data-branch-lng', cmsText(branch.lng));
    button.setAttribute('data-branch-zoom', String(branch.zoom || 15));
    button.setAttribute('data-branch-phone', cmsText(branch.phone));
    button.setAttribute('data-branch-phone-href', cmsText(branch.phone_href));
    button.innerHTML =
      '<span class="icon-badge md"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg></span>' +
      '<span class="branch-body"><span class="branch-head"><span class="branch-name"></span></span>' +
      '<span class="branch-text"></span><span class="branch-cta" data-branch-cta>Show on map</span></span>';
    button.querySelector('.branch-name').textContent = cmsText(branch.name);
    button.querySelector('.branch-text').textContent = cmsText(branch.address);
    if (branch.tag) {
      var tag = document.createElement('span');
      tag.className = 'branch-tag' + (branch.is_primary ? '' : ' is-soft');
      tag.textContent = cmsText(branch.tag);
      button.querySelector('.branch-head').appendChild(tag);
    }
    item.appendChild(button);

    var meta = document.createElement('div');
    meta.className = 'branch-meta';
    if (branch.phone && branch.phone_href) {
      var phone = document.createElement('a');
      phone.href = branch.phone_href;
      phone.textContent = cmsText(branch.phone);
      meta.appendChild(phone);
    }
    if (branch.hours) {
      var hours = document.createElement('span');
      hours.textContent = cmsText(branch.hours);
      meta.appendChild(hours);
    }
    if (meta.childNodes.length) item.appendChild(meta);

    return item;
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

  if (window.fetch && branchPickers.length) {
    fetch(endpointUrl('branches-data.php'), { headers: { 'Accept': 'application/json' } })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (!data || !data.ok || !Array.isArray(data.branches) || data.branches.length === 0) return;

        branchPickers.forEach(function (picker) {
          var list = picker.querySelector('[data-branch-list]');
          if (!list) return;
          list.innerHTML = '';
          data.branches.forEach(function (branch) {
            list.appendChild(createBranchItem(branch));
          });
          // The endpoint lists the main clinic first; show it by default.
          selectBranch(picker, list.querySelector('.branch-select'));
        });
      })
      .catch(function () {
        // Keep the built-in branch list when CMS branches are unavailable.
      });
  }

  /* ---------- CMS-backed site settings ---------------------------------- */
  if (window.fetch) {
    fetch(endpointUrl('site-settings-data.php'), { headers: { 'Accept': 'application/json' } })
      .then(function (response) {
        return response.json();
      })
      .then(applySiteSettings)
      .catch(function () {
        // Keep static contact details when CMS settings are unavailable.
      });
  }

  /* ---------- CMS-backed form options ----------------------------------- */
  if (window.fetch) {
    fetch(endpointUrl('form-options.php'), { headers: { 'Accept': 'application/json' } })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (!data || !data.ok) return;

        function replaceOptions(select, rows, placeholder) {
          if (!select || !Array.isArray(rows) || rows.length === 0) return;
          select.innerHTML = '';
          var empty = document.createElement('option');
          empty.value = '';
          empty.textContent = placeholder;
          select.appendChild(empty);
          rows.forEach(function (row) {
            var option = document.createElement('option');
            option.value = row.value;
            option.textContent = row.label;
            select.appendChild(option);
          });
        }

        replaceOptions(document.getElementById('f-service'), data.specialties, 'Select a service');
        replaceOptions(document.getElementById('c-subject'), data.subjects, 'Select a topic');
      })
      .catch(function () {
        // Keep hardcoded fallback options when CMS options are unavailable.
      });
  }

  /* ---------- CMS-backed homepage specialties --------------------------- */
  var homeSpecialtyList = document.querySelector('[data-cms-home-specialty-list]');
  if (window.fetch && homeSpecialtyList) {
    fetch(endpointUrl('specialties-data.php'), { headers: { 'Accept': 'application/json' } })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (!data || !data.ok || !Array.isArray(data.specialties) || data.specialties.length === 0) return;

        homeSpecialtyList.innerHTML = '';
        data.specialties.slice(0, 6).forEach(function (specialty) {
          homeSpecialtyList.appendChild(createHomeSpecialtyCard(specialty));
        });
      })
      .catch(function () {
        // Keep hardcoded homepage specialty cards when CMS data is unavailable.
      });
  }

  /* ---------- CMS-backed specialties listing ---------------------------- */
  var specialtyNav = document.querySelector('[data-cms-specialty-nav]');
  var specialtyList = document.querySelector('[data-cms-specialty-list]');
  if (window.fetch && specialtyNav && specialtyList) {
    fetch(endpointUrl('specialties-data.php'), { headers: { 'Accept': 'application/json' } })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (!data || !data.ok || !Array.isArray(data.specialties) || data.specialties.length === 0) return;

        function text(value) {
          return value == null ? '' : String(value);
        }

        function iconSvg(size) {
          return '<svg width="' + size + '" height="' + size + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v18"></path><path d="M5 12h14"></path><circle cx="12" cy="12" r="8"></circle></svg>';
        }

        function tickSvg() {
          return '<span class="tick sm"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>';
        }

        specialtyNav.innerHTML = '';
        specialtyList.innerHTML = '';

        data.specialties.forEach(function (specialty) {
          var slug = text(specialty.slug);
          var title = text(specialty.title);
          var description = text(specialty.short_description);

          var navLink = document.createElement('a');
          navLink.href = '#' + slug;
          navLink.innerHTML = '<span class="icon-badge sm">' + iconSvg(22) + '</span><span class="quick-label"></span>';
          navLink.querySelector('.quick-label').textContent = title;
          specialtyNav.appendChild(navLink);

          var row = document.createElement('div');
          row.className = 'spec-row';
          row.id = slug;

          var media = document.createElement('div');
          media.className = 'spec-media';
          var photo = document.createElement('div');
          photo.className = 'photo-ph ratio-4x3';
          if (specialty.image_path) {
            var img = document.createElement('img');
            img.src = specialty.image_path;
            img.alt = text(specialty.image_alt || title);
            img.loading = 'lazy';
            photo.appendChild(img);
          } else {
            var placeholder = document.createElement('span');
            placeholder.textContent = title + ' photo';
            photo.appendChild(placeholder);
          }
          media.appendChild(photo);

          var body = document.createElement('div');
          body.className = 'spec-body';
          var eyebrow = document.createElement('span');
          eyebrow.className = 'eyebrow with-icon';
          eyebrow.innerHTML = '<span class="icon-badge sm">' + iconSvg(22) + '</span>';
          eyebrow.setAttribute('aria-hidden', 'true');
          body.appendChild(eyebrow);

          var heading = document.createElement('h2');
          heading.className = 'section-title';
          heading.textContent = title;
          body.appendChild(heading);

          if (description) {
            var lead = document.createElement('p');
            lead.className = 'section-lead';
            lead.textContent = description;
            body.appendChild(lead);
          }

          if (Array.isArray(specialty.points) && specialty.points.length > 0) {
            var list = document.createElement('ul');
            list.className = 'tick-list compact';
            specialty.points.forEach(function (point) {
              var item = document.createElement('li');
              item.innerHTML = tickSvg();
              item.appendChild(document.createTextNode(text(point)));
              list.appendChild(item);
            });
            body.appendChild(list);
          }

          var action = document.createElement('a');
          action.className = 'link-cta inline';
          action.href = 'home#appointment';
          action.innerHTML = 'Book this service ' + arrowSvg();
          body.appendChild(action);

          row.appendChild(media);
          row.appendChild(body);
          specialtyList.appendChild(row);
        });
      })
      .catch(function () {
        // Keep hardcoded specialty content when CMS data is unavailable.
      });
  }

  /* ---------- CMS-backed doctors listing -------------------------------- */
  // Homepage shows four doctors without filters; filtering lives on the doctors page.
  var homeDoctorList = document.querySelector('[data-cms-home-doctor-list]');
  var homeDoctorFallbackNote = document.querySelector('[data-cms-home-doctor-fallback-note]');
  if (window.fetch && homeDoctorList) {
    fetch(endpointUrl('doctors-data.php'), { headers: { 'Accept': 'application/json' } })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (!data || !data.ok || !Array.isArray(data.doctors) || data.doctors.length === 0) return;

        homeDoctorList.innerHTML = '';
        data.doctors.slice(0, 4).forEach(function (doctor) {
          homeDoctorList.appendChild(createDoctorCard(doctor, true));
        });

        if (homeDoctorFallbackNote) homeDoctorFallbackNote.hidden = true;
      })
      .catch(function () {
        // Keep hardcoded homepage doctor cards when CMS data is unavailable.
      });
  }

  var doctorTabs = document.querySelector('[data-cms-doctor-tabs]');
  var doctorList = document.querySelector('[data-cms-doctor-list]');
  var doctorEmpty = document.querySelector('[data-filter-empty="doctors"]');
  if (window.fetch && doctorTabs && doctorList) {
    fetch(endpointUrl('doctors-data.php'), { headers: { 'Accept': 'application/json' } })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (!data || !data.ok || !Array.isArray(data.doctors) || data.doctors.length === 0) return;

        function applyDoctorFilter(value) {
          var cards = doctorList.querySelectorAll('[data-category]');
          var shown = 0;
          cards.forEach(function (card) {
            var match = value === 'All' || card.getAttribute('data-category') === value;
            card.hidden = !match;
            if (match) shown++;
          });
          if (doctorEmpty) doctorEmpty.hidden = shown !== 0;
        }

        doctorTabs.innerHTML = '';
        doctorList.innerHTML = '';

        ['All'].concat(data.specialties || []).forEach(function (specialty, index) {
          var tab = document.createElement('button');
          tab.type = 'button';
          tab.className = 'tab lg' + (index === 0 ? ' is-active' : '');
          tab.setAttribute('data-filter', specialty);
          tab.setAttribute('aria-pressed', index === 0 ? 'true' : 'false');
          tab.textContent = specialty;
          tab.addEventListener('click', function () {
            doctorTabs.querySelectorAll('[data-filter]').forEach(function (item) {
              var active = item === tab;
              item.classList.toggle('is-active', active);
              item.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            applyDoctorFilter(specialty);
          });
          doctorTabs.appendChild(tab);
        });

        data.doctors.forEach(function (doctor) {
          doctorList.appendChild(createDoctorCard(doctor, false));
        });

        applyDoctorFilter('All');
      })
      .catch(function () {
        // Keep hardcoded doctor content when CMS data is unavailable.
      });
  }

  /* ---------- CMS-backed blog listing ----------------------------------- */
  var homeBlogList = document.querySelector('[data-cms-home-blog-list]');
  var homeBlogFallbackNote = document.querySelector('[data-cms-home-blog-fallback-note]');
  if (window.fetch && homeBlogList) {
    function clearHomeBlogs() {
      homeBlogList.innerHTML = '';
      if (homeBlogFallbackNote) homeBlogFallbackNote.hidden = true;
    }

    fetch(endpointUrl('blog-data.php'), { headers: { 'Accept': 'application/json' } })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (!data || !data.ok || !Array.isArray(data.posts) || data.posts.length === 0) {
          clearHomeBlogs();
          return;
        }

        homeBlogList.innerHTML = '';
        data.posts.slice(0, 3).forEach(function (post) {
          homeBlogList.appendChild(createBlogPostCard(post, false));
        });
        if (homeBlogFallbackNote) homeBlogFallbackNote.hidden = true;
      })
      .catch(function () {
        clearHomeBlogs();
      });
  }

  var featuredBlog = document.querySelector('[data-cms-featured-blog]');
  var blogTabs = document.querySelector('[data-cms-blog-tabs]');
  var blogList = document.querySelector('[data-cms-blog-list]');
  var blogEmpty = document.querySelector('[data-filter-empty="posts"]');
  var blogFallbackNote = document.querySelector('[data-cms-blog-fallback-note]');
  if (window.fetch && featuredBlog && blogTabs && blogList) {
    function clearBlogPage() {
      featuredBlog.innerHTML = '';
      blogTabs.innerHTML = '';
      blogList.innerHTML = '';
      if (blogEmpty) blogEmpty.hidden = false;
      if (blogFallbackNote) blogFallbackNote.hidden = true;
    }

    fetch(endpointUrl('blog-data.php'), { headers: { 'Accept': 'application/json' } })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (!data || !data.ok || !Array.isArray(data.posts) || data.posts.length === 0) {
          clearBlogPage();
          return;
        }

        function applyBlogFilter(value) {
          var cards = blogList.querySelectorAll('[data-category]');
          var shown = 0;
          cards.forEach(function (card) {
            var match = value === 'All' || card.getAttribute('data-category') === value;
            card.hidden = !match;
            if (match) shown++;
          });
          if (blogEmpty) blogEmpty.hidden = shown !== 0;
        }

        blogTabs.innerHTML = '';
        blogList.innerHTML = '';

        ['All'].concat(data.categories || []).forEach(function (category, index) {
          var tab = document.createElement('button');
          tab.type = 'button';
          tab.className = 'tab lg' + (index === 0 ? ' is-active' : '');
          tab.setAttribute('data-filter', category);
          tab.setAttribute('aria-pressed', index === 0 ? 'true' : 'false');
          tab.textContent = category;
          tab.addEventListener('click', function () {
            blogTabs.querySelectorAll('[data-filter]').forEach(function (item) {
              var active = item === tab;
              item.classList.toggle('is-active', active);
              item.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            applyBlogFilter(category);
          });
          blogTabs.appendChild(tab);
        });

        var featuredPost = data.posts.find(function (post) {
          return post.is_featured;
        }) || data.posts[0];

        featuredBlog.innerHTML = '';
        featuredBlog.appendChild(createBlogPostCard(featuredPost, true));

        data.posts.forEach(function (post) {
          blogList.appendChild(createBlogPostCard(post, false));
        });

        applyBlogFilter('All');
        if (blogFallbackNote) blogFallbackNote.hidden = true;
      })
      .catch(function () {
        clearBlogPage();
      });
  }
})();
