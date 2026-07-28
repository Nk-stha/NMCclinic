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
      var panel = document.getElementById(form.getAttribute('data-success'));
      if (!panel) return;
      // The dc source kept the form on screen and revealed a message below the
      // submit button, so the form is deliberately NOT hidden here.
      panel.hidden = false;
      panel.setAttribute('tabindex', '-1');
      panel.focus({ preventScroll: true });
      panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
  });

  /* ---------- current year ---------------------------------------------- */
  document.querySelectorAll('[data-year]').forEach(function (el) {
    el.textContent = String(new Date().getFullYear());
  });
})();
