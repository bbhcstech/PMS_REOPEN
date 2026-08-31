/* ======================================================
   BITROXIA LEADS — shared contact-form -> admin inbox bridge
   Demo-grade persistence: stores submissions in localStorage
   under 'bitroxia_requests' so the Admin dashboard (same browser)
   can read them. Swap this module for real API calls to your
   Laravel backend when you wire up production storage.
   ====================================================== */
window.BitroxiaLeads = (function () {
  var KEY = "bitroxia_requests";

  function getAll() {
    try {
      var raw = localStorage.getItem(KEY);
      return raw ? JSON.parse(raw) : [];
    } catch (e) {
      return [];
    }
  }

  function saveAll(list) {
    try {
      localStorage.setItem(KEY, JSON.stringify(list));
      return true;
    } catch (e) {
      return false;
    }
  }

  function submit(data) {
    var list = getAll();
    var lead = {
      id: "req_" + Date.now() + "_" + Math.floor(Math.random() * 1000),
      name: data.name || "",
      email: data.email || "",
      company: data.company || "",
      teamSize: data.teamSize || "",
      message: data.message || "",
      source: data.source || "Contact Page",
      date: new Date().toISOString(),
      status: "new"
    };
    list.unshift(lead);
    saveAll(list);
    return lead;
  }

  function markRead(id, read) {
    var list = getAll();
    list.forEach(function (l) { if (l.id === id) l.status = read ? "read" : "new"; });
    saveAll(list);
  }

  function remove(id) {
    saveAll(getAll().filter(function (l) { return l.id !== id; }));
  }

  function clearAll() {
    saveAll([]);
  }

  /* Wires a standard contact <form> to store submissions + show an
     inline success/error alert. Expects the form to contain fields
     named/ided: name, email, company, teamSize, message (any that
     exist are read; none are required except name + email). */
  function wireForm(form, alertEl, sourceLabel) {
    if (!form) return;
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var get = function (sel) {
        var el = form.querySelector(sel);
        return el ? el.value.trim() : "";
      };
      var name = get('[data-field="name"]');
      var email = get('[data-field="email"]');
      var company = get('[data-field="company"]');
      var teamSize = get('[data-field="teamSize"]');
      var message = get('[data-field="message"]');

      if (!name || !email) {
        showAlert(alertEl, "is-error", "Please fill in your name and email before sending.");
        return;
      }

      submit({
        name: name, email: email, company: company,
        teamSize: teamSize, message: message,
        source: sourceLabel || document.title
      });

      showAlert(alertEl, "is-success", "Thanks, " + name.split(" ")[0] + " — your request has been sent. Our team will follow up shortly.");
      form.reset();
    });
  }

  function showAlert(alertEl, cls, text) {
    if (!alertEl) return;
    alertEl.classList.remove("is-success", "is-error");
    alertEl.classList.add(cls, "is-visible");
    var textEl = alertEl.querySelector("span");
    if (textEl) textEl.textContent = text;
    else alertEl.textContent = text;
    alertEl.scrollIntoView({ behavior: "smooth", block: "center" });
  }

  return {
    getAll: getAll,
    submit: submit,
    markRead: markRead,
    remove: remove,
    clearAll: clearAll,
    wireForm: wireForm
  };
})();
