/* ======================================================
   BITROXIA ADMIN — demo auth + dashboard
   Demo credentials only (client-side check for prototype
   purposes). Replace with real authentication against your
   backend before this leaves staging.
   ====================================================== */
(function () {
  "use strict";
  var AUTH_KEY = "bitroxia_admin_auth";
  var DEMO_EMAIL = "admin@bitroxia.com";
  var DEMO_PASS = "bitroxia@2026";

  /* ---- LOGIN PAGE ---- */
  var loginForm = document.getElementById("adminLoginForm");
  if (loginForm) {
    // Already logged in this session? Skip straight to dashboard.
    if (sessionStorage.getItem(AUTH_KEY) === "1") {
      window.location.href = "dashboard.html";
    }
    var errorBox = document.getElementById("adminError");
    loginForm.addEventListener("submit", function (e) {
      e.preventDefault();
      var email = document.getElementById("adminEmail").value.trim();
      var pass = document.getElementById("adminPass").value;
      if (email === DEMO_EMAIL && pass === DEMO_PASS) {
        sessionStorage.setItem(AUTH_KEY, "1");
        window.location.href = "dashboard.html";
      } else {
        if (errorBox) errorBox.classList.add("is-visible");
      }
    });
  }

  /* ---- DASHBOARD PAGE ---- */
  var dashRoot = document.getElementById("adminDashboard");
  if (dashRoot) {
    if (sessionStorage.getItem(AUTH_KEY) !== "1") {
      window.location.href = "login.html";
      return;
    }

    var logoutBtn = document.getElementById("adminLogout");
    if (logoutBtn) {
      logoutBtn.addEventListener("click", function () {
        sessionStorage.removeItem(AUTH_KEY);
        window.location.href = "login.html";
      });
    }

    var sidebarToggle = document.getElementById("adminSidebarToggle");
    var sidebar = document.getElementById("adminSidebar");
    if (sidebarToggle && sidebar) {
      sidebarToggle.addEventListener("click", function () {
        sidebar.classList.toggle("is-open");
      });
    }

    var tableBody = document.getElementById("leadsTableBody");
    var emptyState = document.getElementById("leadsEmpty");
    var searchInput = document.getElementById("leadsSearch");
    var filterBtns = document.querySelectorAll("[data-filter]");
    var statTotal = document.getElementById("statTotal");
    var statNew = document.getElementById("statNew");
    var statRead = document.getElementById("statRead");
    var statWeek = document.getElementById("statWeek");
    var navCountNew = document.getElementById("navCountNew");

    var currentFilter = "all";
    var currentSearch = "";

    function initials(name) {
      var parts = (name || "?").trim().split(/\s+/);
      return ((parts[0] || "")[0] || "?").toUpperCase() + ((parts[1] || "")[0] || "").toUpperCase();
    }

    function formatDate(iso) {
      var d = new Date(iso);
      return d.toLocaleDateString(undefined, { day: "numeric", month: "short", year: "numeric" }) +
        " · " + d.toLocaleTimeString(undefined, { hour: "2-digit", minute: "2-digit" });
    }

    function render() {
      var all = window.BitroxiaLeads.getAll();

      var total = all.length;
      var newCount = all.filter(function (l) { return l.status === "new"; }).length;
      var readCount = total - newCount;
      var weekAgo = Date.now() - 7 * 24 * 60 * 60 * 1000;
      var weekCount = all.filter(function (l) { return new Date(l.date).getTime() >= weekAgo; }).length;

      if (statTotal) statTotal.textContent = total;
      if (statNew) statNew.textContent = newCount;
      if (statRead) statRead.textContent = readCount;
      if (statWeek) statWeek.textContent = weekCount;
      if (navCountNew) navCountNew.textContent = newCount;

      var filtered = all.filter(function (l) {
        if (currentFilter === "new" && l.status !== "new") return false;
        if (currentFilter === "read" && l.status !== "read") return false;
        if (currentSearch) {
          var hay = (l.name + " " + l.email + " " + l.company + " " + l.message).toLowerCase();
          if (hay.indexOf(currentSearch.toLowerCase()) === -1) return false;
        }
        return true;
      });

      if (!tableBody) return;
      tableBody.innerHTML = "";

      if (!filtered.length) {
        if (emptyState) emptyState.style.display = "block";
        return;
      }
      if (emptyState) emptyState.style.display = "none";

      filtered.forEach(function (lead) {
        var tr = document.createElement("tr");
        tr.className = lead.status === "read" ? "is-read" : "";
        tr.innerHTML =
          '<td><div class="admin-row-name">' +
            '<span class="admin-row-avatar">' + initials(lead.name) + '</span>' +
            '<div><b>' + escapeHtml(lead.name) + '</b><span>' + escapeHtml(lead.email) + '</span></div>' +
          '</div></td>' +
          '<td>' + escapeHtml(lead.company || "—") + '</td>' +
          '<td>' + escapeHtml(lead.teamSize || "—") + '</td>' +
          '<td>' + escapeHtml(lead.source || "—") + '</td>' +
          '<td>' + formatDate(lead.date) + '</td>' +
          '<td><span class="admin-badge ' + lead.status + '">' + (lead.status === "new" ? "New" : "Read") + '</span></td>' +
          '<td><div class="admin-row-actions">' +
            '<button data-view="' + lead.id + '" aria-label="View request"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg></button>' +
            '<button data-toggle="' + lead.id + '" aria-label="Toggle read status"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></button>' +
            '<button data-delete="' + lead.id + '" class="danger" aria-label="Delete request"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0l-1 14a2 2 0 01-2 2H7a2 2 0 01-2-2L4 6"/></svg></button>' +
          '</div></td>';
        tableBody.appendChild(tr);
      });
    }

    function escapeHtml(str) {
      return String(str || "").replace(/[&<>"']/g, function (c) {
        return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
      });
    }

    if (searchInput) {
      searchInput.addEventListener("input", function () {
        currentSearch = searchInput.value;
        render();
      });
    }
    filterBtns.forEach(function (btn) {
      btn.addEventListener("click", function () {
        filterBtns.forEach(function (b) { b.classList.remove("is-active"); });
        btn.classList.add("is-active");
        currentFilter = btn.getAttribute("data-filter");
        render();
      });
    });

    /* ---- Modal ---- */
    var modalScrim = document.getElementById("leadModal");
    var modalClose = document.getElementById("leadModalClose");
    function openModal(lead) {
      if (!modalScrim) return;
      document.getElementById("modalName").textContent = lead.name || "—";
      document.getElementById("modalEmail").textContent = lead.email || "—";
      document.getElementById("modalCompany").textContent = lead.company || "—";
      document.getElementById("modalTeamSize").textContent = lead.teamSize || "—";
      document.getElementById("modalSource").textContent = lead.source || "—";
      document.getElementById("modalDate").textContent = formatDate(lead.date);
      document.getElementById("modalMessage").textContent = lead.message || "No message provided.";
      modalScrim.classList.add("is-open");
      modalScrim.dataset.leadId = lead.id;
    }
    function closeModal() {
      if (modalScrim) modalScrim.classList.remove("is-open");
    }
    if (modalClose) modalClose.addEventListener("click", closeModal);
    if (modalScrim) {
      modalScrim.addEventListener("click", function (e) {
        if (e.target === modalScrim) closeModal();
      });
    }
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") closeModal();
    });

    var modalMarkRead = document.getElementById("modalMarkRead");
    var modalDelete = document.getElementById("modalDelete");
    if (modalMarkRead) {
      modalMarkRead.addEventListener("click", function () {
        var id = modalScrim.dataset.leadId;
        window.BitroxiaLeads.markRead(id, true);
        closeModal();
        render();
      });
    }
    if (modalDelete) {
      modalDelete.addEventListener("click", function () {
        var id = modalScrim.dataset.leadId;
        window.BitroxiaLeads.remove(id);
        closeModal();
        render();
      });
    }

    if (tableBody) {
      tableBody.addEventListener("click", function (e) {
        var viewId = e.target.closest("[data-view]");
        var toggleId = e.target.closest("[data-toggle]");
        var delId = e.target.closest("[data-delete]");
        var all = window.BitroxiaLeads.getAll();

        if (viewId) {
          var id = viewId.getAttribute("data-view");
          var lead = all.find(function (l) { return l.id === id; });
          if (lead) {
            window.BitroxiaLeads.markRead(id, true);
            openModal(lead);
            render();
          }
        }
        if (toggleId) {
          var tid = toggleId.getAttribute("data-toggle");
          var tlead = all.find(function (l) { return l.id === tid; });
          if (tlead) {
            window.BitroxiaLeads.markRead(tid, tlead.status !== "read");
            render();
          }
        }
        if (delId) {
          var did = delId.getAttribute("data-delete");
          if (window.confirm("Delete this request? This cannot be undone.")) {
            window.BitroxiaLeads.remove(did);
            render();
          }
        }
      });
    }

    render();
  }
})();
