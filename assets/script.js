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

    var syncSolid = function () {
      var scrolled = (window.scrollY || window.pageYOffset || 0) > 24;
      header.classList.toggle('is-solid', scrolled || header.classList.contains('is-open'));
    };

    window.addEventListener('scroll', syncSolid, { passive: true });
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
    var mq = window.matchMedia('(min-width: 1180px)');
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

  /* ---------- current year ---------------------------------------------- */
  document.querySelectorAll('[data-year]').forEach(function (el) {
    el.textContent = String(new Date().getFullYear());
  });

  /* ---------- CMS-backed form options ----------------------------------- */
  if (window.fetch) {
    fetch('form-options.php', { headers: { 'Accept': 'application/json' } })
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

  /* ---------- CMS-backed specialties listing ---------------------------- */
  var specialtyNav = document.querySelector('[data-cms-specialty-nav]');
  var specialtyList = document.querySelector('[data-cms-specialty-list]');
  if (window.fetch && specialtyNav && specialtyList) {
    fetch('specialties-data.php', { headers: { 'Accept': 'application/json' } })
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
          eyebrow.appendChild(document.createTextNode(title));
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

          var actions = document.createElement('div');
          actions.className = 'btn-row';
          actions.innerHTML = '<a class="btn btn-primary btn-sm" href="index.html#appointment">Book an Appointment</a><a class="link-cta" href="doctors.html">Find a specialist</a>';
          body.appendChild(actions);

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
  var doctorTabs = document.querySelector('[data-cms-doctor-tabs]');
  var doctorList = document.querySelector('[data-cms-doctor-list]');
  var doctorEmpty = document.querySelector('[data-filter-empty="doctors"]');
  if (window.fetch && doctorTabs && doctorList) {
    fetch('doctors-data.php', { headers: { 'Accept': 'application/json' } })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (!data || !data.ok || !Array.isArray(data.doctors) || data.doctors.length === 0) return;

        function text(value) {
          return value == null ? '' : String(value);
        }

        function calendarSvg() {
          return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#287772" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>';
        }

        function scheduleText(schedules) {
          if (!Array.isArray(schedules) || schedules.length === 0) return 'Schedule to be confirmed';
          return schedules.map(function (schedule) {
            var time = schedule.start_time && schedule.end_time ? ' ' + schedule.start_time + '-' + schedule.end_time : '';
            var location = schedule.location ? ' · ' + schedule.location : '';
            return schedule.day + time + location;
          }).join(', ');
        }

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
          var card = document.createElement('article');
          card.className = 'doc-card';
          card.setAttribute('data-category', text(doctor.specialty));

          var photo = document.createElement('div');
          photo.className = 'doc-photo';
          if (doctor.photo_path) {
            var img = document.createElement('img');
            img.src = doctor.photo_path;
            img.alt = text(doctor.photo_alt || doctor.full_name);
            img.loading = 'lazy';
            photo.appendChild(img);
          } else {
            var placeholder = document.createElement('span');
            placeholder.className = 'ph-label';
            placeholder.textContent = 'doctor photo';
            photo.appendChild(placeholder);
          }
          var tag = document.createElement('span');
          tag.className = 'doc-tag';
          tag.textContent = text(doctor.specialty);
          photo.appendChild(tag);

          var body = document.createElement('div');
          body.className = 'doc-body';
          var name = document.createElement('h3');
          name.textContent = text(doctor.full_name);
          var qual = document.createElement('p');
          qual.className = 'doc-qual';
          qual.textContent = Array.isArray(doctor.qualifications) && doctor.qualifications.length > 0 ? doctor.qualifications.join(', ') : 'Qualification to be confirmed';
          var reg = document.createElement('p');
          reg.className = 'doc-reg';
          reg.textContent = doctor.registration_number ? 'NMC Reg. No. ' + doctor.registration_number : 'NMC Reg. No. to be confirmed';
          var hours = document.createElement('div');
          hours.className = 'doc-hours';
          hours.innerHTML = calendarSvg() + ' ';
          hours.appendChild(document.createTextNode(scheduleText(doctor.schedules)));
          var actions = document.createElement('div');
          actions.className = 'doc-actions';
          actions.innerHTML = '<a class="doc-view-solid" href="doctors.html">View Profile</a><a class="doc-book" href="index.html#appointment">Book Appointment</a>';

          body.appendChild(name);
          body.appendChild(qual);
          body.appendChild(reg);
          body.appendChild(hours);
          body.appendChild(actions);
          card.appendChild(photo);
          card.appendChild(body);
          doctorList.appendChild(card);
        });

        applyDoctorFilter('All');
      })
      .catch(function () {
        // Keep hardcoded doctor content when CMS data is unavailable.
      });
  }

  /* ---------- CMS-backed blog listing ----------------------------------- */
  var featuredBlog = document.querySelector('[data-cms-featured-blog]');
  var blogTabs = document.querySelector('[data-cms-blog-tabs]');
  var blogList = document.querySelector('[data-cms-blog-list]');
  var blogEmpty = document.querySelector('[data-filter-empty="posts"]');
  var blogFallbackNote = document.querySelector('[data-cms-blog-fallback-note]');
  if (window.fetch && featuredBlog && blogTabs && blogList) {
    fetch('blog-data.php', { headers: { 'Accept': 'application/json' } })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (!data || !data.ok || !Array.isArray(data.posts) || data.posts.length === 0) return;

        function text(value) {
          return value == null ? '' : String(value);
        }

        function readTime(minutes) {
          return minutes ? minutes + ' min read' : 'Health guide';
        }

        function byline(post) {
          var reviewer = text(post.reviewer_name) || 'NMC medical team';
          var reviewed = text(post.reviewed_at || post.published_at);
          return 'Reviewed by ' + reviewer + (reviewed ? ' · Reviewed ' + reviewed : '');
        }

        function arrowSvg() {
          return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>';
        }

        function addImage(container, post, fallback) {
          if (post.image_path) {
            var img = document.createElement('img');
            img.src = post.image_path;
            img.alt = text(post.image_alt || post.title);
            img.loading = 'lazy';
            container.appendChild(img);
            return;
          }

          var label = document.createElement('span');
          label.className = 'ph-label';
          label.textContent = fallback;
          container.appendChild(label);
        }

        function createPostCard(post, featured) {
          var card = document.createElement('a');
          card.className = featured ? 'featured' : 'post-card';
          card.href = post.url || ('blog/' + encodeURIComponent(text(post.slug)));
          card.setAttribute('data-category', text(post.category));

          var photo = document.createElement('div');
          photo.className = featured ? 'featured-photo' : 'post-photo';
          addImage(photo, post, featured ? 'featured article image' : 'article image');
          var tag = document.createElement('span');
          tag.className = 'doc-tag';
          tag.textContent = (featured ? 'Featured · ' : '') + text(post.category);
          photo.appendChild(tag);

          var body = document.createElement('div');
          body.className = featured ? 'featured-body' : 'post-body';

          var meta = document.createElement('div');
          meta.className = 'post-meta';
          meta.textContent = readTime(post.reading_time_minutes);
          var title = document.createElement(featured ? 'h2' : 'h3');
          title.textContent = text(post.title);
          var excerpt = document.createElement('p');
          excerpt.textContent = text(post.excerpt);
          var review = document.createElement('div');
          review.className = 'post-byline';
          review.textContent = byline(post);
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
        featuredBlog.appendChild(createPostCard(featuredPost, true));

        data.posts.forEach(function (post) {
          blogList.appendChild(createPostCard(post, false));
        });

        applyBlogFilter('All');
        if (blogFallbackNote) blogFallbackNote.hidden = true;
      })
      .catch(function () {
        // Keep hardcoded blog content when CMS data is unavailable.
      });
  }
})();
