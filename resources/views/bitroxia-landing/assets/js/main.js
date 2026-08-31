(function () {
  "use strict";

  /* ============ THEME TOGGLE ============ */
  var root = document.documentElement;
  var themeToggle = document.getElementById("themeToggle");
  var savedTheme = localStorage.getItem("bitroxia-theme");
  if (savedTheme === "light" || savedTheme === "dark") {
    root.setAttribute("data-theme", savedTheme);
  } else if (window.matchMedia && window.matchMedia("(prefers-color-scheme: light)").matches) {
    root.setAttribute("data-theme", "light");
  }
  function syncThemeToggle() {
    var isLight = root.getAttribute("data-theme") === "light";
    if (themeToggle) themeToggle.setAttribute("aria-pressed", String(isLight));
  }
  syncThemeToggle();
  if (themeToggle) {
    themeToggle.addEventListener("click", function () {
      var next = root.getAttribute("data-theme") === "light" ? "dark" : "light";
      root.setAttribute("data-theme", next);
      localStorage.setItem("bitroxia-theme", next);
      syncThemeToggle();
    });
  }

  /* ============ STICKY NAV ============ */
  var nav = document.getElementById("siteNav");
  function onScroll() {
    if (!nav) return;
    if (window.scrollY > 24) nav.classList.add("is-scrolled");
    else nav.classList.remove("is-scrolled");

    var backToTop = document.getElementById("backToTop");
    if (backToTop) {
      if (window.scrollY > 700) backToTop.classList.add("is-visible");
      else backToTop.classList.remove("is-visible");
    }
  }
  document.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  var backToTop = document.getElementById("backToTop");
  if (backToTop) {
    backToTop.addEventListener("click", function () {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }

  /* ============ DESKTOP MEGA DROPDOWNS ============ */
  var navItems = document.querySelectorAll(".nav-links > li");
  navItems.forEach(function (item) {
    var btn = item.querySelector(".nav-top-link");
    if (!btn) return;
    btn.addEventListener("click", function (e) {
      e.stopPropagation();
      var wasOpen = item.classList.contains("is-open");
      navItems.forEach(function (i) {
        i.classList.remove("is-open");
        var b = i.querySelector(".nav-top-link");
        if (b) b.setAttribute("aria-expanded", "false");
      });
      if (!wasOpen) {
        item.classList.add("is-open");
        btn.setAttribute("aria-expanded", "true");
      }
    });
  });
  document.addEventListener("click", function () {
    navItems.forEach(function (i) {
      i.classList.remove("is-open");
      var b = i.querySelector(".nav-top-link");
      if (b) b.setAttribute("aria-expanded", "false");
    });
  });
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      navItems.forEach(function (i) { i.classList.remove("is-open"); });
    }
  });

  /* ============ MOBILE DRAWER ============ */
  var hamburgerBtn = document.getElementById("hamburgerBtn");
  var mobileDrawer = document.getElementById("mobileDrawer");
  var body = document.body;

  function openDrawer() {
    body.classList.add("nav-open");
    if (hamburgerBtn) hamburgerBtn.setAttribute("aria-expanded", "true");
  }
  function closeDrawer() {
    body.classList.remove("nav-open");
    if (hamburgerBtn) hamburgerBtn.setAttribute("aria-expanded", "false");
  }
  if (hamburgerBtn) {
    hamburgerBtn.addEventListener("click", function () {
      if (body.classList.contains("nav-open")) closeDrawer();
      else openDrawer();
    });
  }
  if (mobileDrawer) {
    mobileDrawer.querySelectorAll("[data-close-drawer]").forEach(function (el) {
      el.addEventListener("click", closeDrawer);
    });
  }
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") closeDrawer();
  });

  /* ============ SCROLL REVEAL ============ */
  var revealEls = document.querySelectorAll("[data-reveal]");
  if ("IntersectionObserver" in window && revealEls.length) {
    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.12, rootMargin: "0px 0px -60px 0px" }
    );
    revealEls.forEach(function (el) { io.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add("is-visible"); });
  }

  /* ============ STAT COUNT-UP ============ */
  var counters = document.querySelectorAll("[data-count]");
  function animateCount(el) {
    var target = parseInt(el.getAttribute("data-count"), 10) || 0;
    var suffix = el.getAttribute("data-suffix") || "";
    var duration = 1200;
    var start = null;
    function step(ts) {
      if (!start) start = ts;
      var progress = Math.min((ts - start) / duration, 1);
      var eased = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.round(eased * target) + suffix;
      if (progress < 1) requestAnimationFrame(step);
      else el.textContent = target + suffix;
    }
    requestAnimationFrame(step);
  }
  if ("IntersectionObserver" in window && counters.length) {
    var cio = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            animateCount(entry.target);
            cio.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.5 }
    );
    counters.forEach(function (el) { cio.observe(el); });
  }

  /* ============ TESTIMONIAL CAROUSEL ============ */
  var slidesWrap = document.getElementById("testiSlides");
  var dotsWrap = document.getElementById("testiDots");
  var prevBtn = document.getElementById("testiPrev");
  var nextBtn = document.getElementById("testiNext");

  if (slidesWrap && dotsWrap) {
    var slides = slidesWrap.querySelectorAll(".testi-slide");
    var current = 0;
    var autoTimer;

    slides.forEach(function (_, idx) {
      var dot = document.createElement("button");
      dot.setAttribute("aria-label", "Go to testimonial " + (idx + 1));
      if (idx === 0) dot.classList.add("is-active");
      dot.addEventListener("click", function () { goTo(idx); resetAuto(); });
      dotsWrap.appendChild(dot);
    });
    var dots = dotsWrap.querySelectorAll("button");

    function goTo(idx) {
      current = (idx + slides.length) % slides.length;
      slidesWrap.style.transform = "translateX(-" + current * 100 + "%)";
      dots.forEach(function (d, i) { d.classList.toggle("is-active", i === current); });
    }
    function resetAuto() {
      clearInterval(autoTimer);
      autoTimer = setInterval(function () { goTo(current + 1); }, 6000);
    }
    if (prevBtn) prevBtn.addEventListener("click", function () { goTo(current - 1); resetAuto(); });
    if (nextBtn) nextBtn.addEventListener("click", function () { goTo(current + 1); resetAuto(); });
    goTo(0);
    resetAuto();
  }

  /* ============ FAQ ACCORDION ============ */
  var faqItems = document.querySelectorAll(".faq-item");
  function setFaqState(item, open) {
    var a = item.querySelector(".faq-a");
    if (open) {
      item.classList.add("is-open");
      a.style.maxHeight = a.scrollHeight + "px";
    } else {
      item.classList.remove("is-open");
      a.style.maxHeight = 0;
    }
  }
  faqItems.forEach(function (item) {
    setFaqState(item, item.classList.contains("is-open"));
    var q = item.querySelector(".faq-q");
    q.addEventListener("click", function () {
      var willOpen = !item.classList.contains("is-open");
      faqItems.forEach(function (other) { setFaqState(other, false); });
      setFaqState(item, willOpen);
    });
  });
  window.addEventListener("resize", function () {
    faqItems.forEach(function (item) {
      if (item.classList.contains("is-open")) {
        var a = item.querySelector(".faq-a");
        a.style.maxHeight = a.scrollHeight + "px";
      }
    });
  });

  /* ============ VIDEO PLAYER ============ */
  var videoWrap = document.getElementById("videoWrap");
  var demoVideo = document.getElementById("demoVideo");
  var playBtn = document.getElementById("playBtn");
  var pauseBtn = document.getElementById("pauseBtn");
  var muteBtn = document.getElementById("muteBtn");
  var progressBar = document.getElementById("videoProgress");

  if (videoWrap && demoVideo && playBtn) {
    playBtn.addEventListener("click", function () {
      videoWrap.classList.add("is-playing");
      demoVideo.play().catch(function () {
        /* No video source provided yet — keep controls visible so the
           team can wire assets/video/bitroxia-demo.mp4 later. */
      });
    });
    if (pauseBtn) {
      pauseBtn.addEventListener("click", function () {
        if (demoVideo.paused) { demoVideo.play(); } else { demoVideo.pause(); }
      });
    }
    if (muteBtn) {
      muteBtn.addEventListener("click", function () {
        demoVideo.muted = !demoVideo.muted;
      });
    }
    demoVideo.addEventListener("timeupdate", function () {
      if (demoVideo.duration && progressBar) {
        progressBar.style.width = (demoVideo.currentTime / demoVideo.duration) * 100 + "%";
      }
    });
    demoVideo.addEventListener("ended", function () {
      videoWrap.classList.remove("is-playing");
      if (progressBar) progressBar.style.width = "0%";
    });
  }

  /* ============ ACTIVE NAV LINK HIGHLIGHT ============ */
  (function highlightActiveNav() {
    var here = window.location.pathname.split("/").pop() || "index.html";
    document.querySelectorAll('.nav-links a[href], .mobile-links a[href]').forEach(function (a) {
      var hrefFile = a.getAttribute("href").split("#")[0].split("/").pop();
      if (hrefFile && hrefFile === here) {
        a.classList.add("is-current");
      }
    });
  })();

  /* ============ JUMP-NAV SCROLL SPY (Features/Solutions/Resources pages) ============ */
  var jumpLinks = document.querySelectorAll(".jump-row a");
  if (jumpLinks.length && "IntersectionObserver" in window) {
    var jumpTargets = [];
    jumpLinks.forEach(function (a) {
      var id = a.getAttribute("href").slice(1);
      var el = document.getElementById(id);
      if (el) jumpTargets.push({ link: a, el: el });
    });
    var jio = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          var match = jumpTargets.find(function (t) { return t.el === entry.target; });
          if (match && entry.isIntersecting) {
            jumpLinks.forEach(function (l) { l.classList.remove("is-active"); });
            match.link.classList.add("is-active");
          }
        });
      },
      { rootMargin: "-45% 0px -50% 0px", threshold: 0 }
    );
    jumpTargets.forEach(function (t) { jio.observe(t.el); });
  }

  /* ============ SMOOTH ANCHOR FOR IN-PAGE LINKS ============ */
  document.querySelectorAll('a[href^="#"]').forEach(function (link) {
    link.addEventListener("click", function (e) {
      var id = link.getAttribute("href");
      if (id.length > 1) {
        var target = document.querySelector(id);
        if (target) {
          e.preventDefault();
          closeDrawer();
          var offset = 84;
          var top = target.getBoundingClientRect().top + window.pageYOffset - offset;
          window.scrollTo({ top: top, behavior: "smooth" });
        }
      }
    });
  });
})();
