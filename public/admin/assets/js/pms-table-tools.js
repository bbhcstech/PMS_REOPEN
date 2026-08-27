(function () {
  'use strict';

  var controllerNumber = 0;
  var cardNumber = 0;
  var controllers = new WeakMap();
  var actionHeaderPattern = /^(action|actions|option|options|control|controls|manage|management|operation|operations)$/i;
  var cardSelector = '.card, [data-pms-card-border], div[class*="-card"], section[class*="-card"], article[class*="-card"], aside[class*="-card"], li[class*="-card"], a[class*="-card"], form[class*="-card"]';

  function icon(name) {
    if (name === 'download') {
      return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3v11m0 0 4-4m-4 4-4-4M5 17v3h14v-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    }
    if (name === 'rows') {
      return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 6h14M5 12h14M5 18h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';
    }
    return '<svg class="pms-table-export__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m7 10 5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
  }

  function isEligible(table) {
    if (!table || table.tagName !== 'TABLE') return false;
    if (table.matches('[data-pms-export="off"], [role="presentation"]')) return false;
    if (table.closest('[data-pms-export="off"]')) return false;
    if (!table.tHead || !table.tBodies.length) return false;
    return Boolean(table.tHead.querySelector('th'));
  }

  function dataRows(table) {
    var controller = controllers.get(table);
    var rows = [];

    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable && window.jQuery.fn.DataTable.isDataTable(table)) {
      try {
        rows = window.jQuery(table).DataTable().rows({ search: 'none', page: 'all' }).nodes().toArray();
      } catch (error) {
        rows = [];
      }
    }

    if (!rows.length) {
      Array.prototype.forEach.call(table.tBodies, function (body) {
        rows = rows.concat(Array.prototype.slice.call(body.rows));
      });
    }

    return rows.filter(function (row) {
      if (!row || row.classList.contains('child') || row.classList.contains('dtrg-group')) return false;
      if (row.querySelector('td.dataTables_empty')) return false;
      if (row.cells.length === 1 && row.cells[0].colSpan > 1) return false;
      if (controller && controller.placeholderRows.has(row)) return false;
      return Boolean(row.querySelector('td, th'));
    });
  }

  function headerRow(table) {
    if (!table.tHead || !table.tHead.rows.length) return null;
    var rows = Array.prototype.slice.call(table.tHead.rows);
    return rows.reduce(function (best, row) {
      return !best || row.cells.length > best.cells.length ? row : best;
    }, null);
  }

  function checkboxMarkup(kind, tableId) {
    var label = kind === 'all' ? 'Select all rows in this table' : 'Select this row for export';
    return '<label class="pms-table-select" title="' + label + '">' +
      '<input class="pms-table-select__input" type="checkbox" data-pms-select="' + kind + '" data-pms-table="' + tableId + '" aria-label="' + label + '">' +
      '</label>';
  }

  function hasLegacySelectionColumn(table) {
    var head = headerRow(table);
    if (!head || !head.cells.length) return false;
    var firstHeader = head.cells[0];
    if (firstHeader.querySelector('input[type="checkbox"]:not([data-pms-select])')) return true;

    var headerLabel = (firstHeader.textContent || '').replace(/\s+/g, ' ').trim();
    if (headerLabel) return false;

    return Array.prototype.some.call(table.tBodies, function (body) {
      return Array.prototype.some.call(body.rows, function (row) {
        return row.cells.length > 1 && Boolean(row.cells[0].querySelector('input[type="checkbox"]:not([data-pms-select])'));
      });
    });
  }

  function removeLegacySelectionControls(controller, cell) {
    if (!controller.legacySelectionColumn || !cell) return;
    var inputs = cell.querySelectorAll('input[type="checkbox"]:not([data-pms-select])');

    Array.prototype.forEach.call(inputs, function (input) {
      var wrapper = input.closest('label, .form-check, .custom-control');
      input.remove();

      if (wrapper && cell.contains(wrapper) && !wrapper.querySelector('input, button, select, textarea, a') && !(wrapper.textContent || '').trim()) {
        wrapper.remove();
      }
    });
  }

  function ensureTableScrolling(controller) {
    var table = controller.table;
    var head = headerRow(table);
    var columnCount = 0;

    if (head) {
      Array.prototype.forEach.call(head.cells, function (cell) {
        columnCount += Math.max(1, Number(cell.colSpan) || 1);
      });
    }

    var minimumWidth = Math.min(2200, Math.max(720, columnCount * 155));
    table.style.setProperty('--pms-table-content-min-width', minimumWidth + 'px');

    var scrollHost = table.closest('.table-responsive') ||
      table.closest('.dataTables_scrollBody') ||
      table.closest('.dataTables_wrapper') ||
      table.parentElement;

    if (scrollHost) {
      scrollHost.classList.add('pms-table-scroll-host');
      controller.scrollHost = scrollHost;
    }
  }

  function protectCheckbox(cell) {
    var label = cell.querySelector(':scope > .pms-table-select');
    if (!label || label._pmsEventsBound) return;
    label._pmsEventsBound = true;
    label.addEventListener('click', function (event) { event.stopPropagation(); });
  }

  function createToolbar(controller) {
    var toolbar = document.createElement('div');
    toolbar.className = 'pms-table-tools';
    toolbar.setAttribute('data-pms-table-tools', controller.id);
    toolbar.innerHTML =
      '<span class="pms-table-tools__status" aria-live="polite">0 rows selected</span>' +
      '<div class="pms-table-export">' +
        '<button class="pms-table-export__toggle" type="button" aria-haspopup="menu" aria-expanded="false">' +
          icon('download') + '<span>Export</span>' + icon('chevron') +
        '</button>' +
        '<div class="pms-table-export__menu" role="menu">' +
          '<button class="pms-table-export__option" type="button" role="menuitem" data-export-scope="all">' + icon('rows') + '<span>Export all</span></button>' +
          '<button class="pms-table-export__option" type="button" role="menuitem" data-export-scope="selected">' + icon('download') + '<span>Export selected</span></button>' +
        '</div>' +
      '</div>';

    var responsiveParent = controller.table.closest('.table-responsive');
    var dataTableWrapper = controller.table.closest('.dataTables_wrapper');
    var anchor = responsiveParent || dataTableWrapper || controller.table;
    var parent = anchor.parentNode;
    ensureTableScrolling(controller);
    if (parent) parent.insertBefore(toolbar, anchor);
    controller.toolbar = toolbar;
  }

  function addHeaderCheckbox(controller) {
    var row = headerRow(controller.table);
    if (!row || !row.cells.length) return;
    var cell = row.cells[0];
    removeLegacySelectionControls(controller, cell);
    cell.classList.add('pms-table-selection-cell');
    if (cell.querySelector('[data-pms-select="all"]')) {
      controller.selectAll = cell.querySelector('[data-pms-select="all"]');
      protectCheckbox(cell);
      return;
    }
    cell.insertAdjacentHTML('afterbegin', checkboxMarkup('all', controller.id));
    controller.selectAll = cell.querySelector('[data-pms-select="all"]');
    protectCheckbox(cell);
  }

  function addRowCheckbox(controller, row) {
    if (!row || !row.cells.length) return;
    if (row.cells.length === 1 && row.cells[0].colSpan > 1) {
      controller.placeholderRows.add(row);
      return;
    }
    var cell = row.cells[0];
    removeLegacySelectionControls(controller, cell);
    cell.classList.add('pms-table-selection-cell');
    var input = cell.querySelector(':scope > .pms-table-select > [data-pms-select="row"]');
    if (!input) {
      cell.insertAdjacentHTML('afterbegin', checkboxMarkup('row', controller.id));
      input = cell.querySelector(':scope > .pms-table-select > [data-pms-select="row"]');
    }
    if (!input) return;
    protectCheckbox(cell);
    input.checked = controller.selectedRows.has(row);
    row.classList.toggle('pms-row-selected', input.checked);
  }

  function syncRows(controller) {
    ensureTableScrolling(controller);
    addHeaderCheckbox(controller);
    dataRows(controller.table).forEach(function (row) { addRowCheckbox(controller, row); });
    syncToolbar(controller);
  }

  function syncToolbar(controller, message) {
    var rows = dataRows(controller.table);
    var selected = rows.filter(function (row) { return controller.selectedRows.has(row); });

    controller.selectedRows.forEach(function (row) {
      if (!document.documentElement.contains(row) && rows.indexOf(row) === -1) controller.selectedRows.delete(row);
    });

    if (controller.selectAll) {
      controller.selectAll.disabled = rows.length === 0;
      controller.selectAll.checked = rows.length > 0 && selected.length === rows.length;
      controller.selectAll.indeterminate = selected.length > 0 && selected.length < rows.length;
    }

    if (!controller.toolbar) return;
    var status = controller.toolbar.querySelector('.pms-table-tools__status');
    var allLabel = controller.toolbar.querySelector('[data-export-scope="all"] span');
    var selectedButton = controller.toolbar.querySelector('[data-export-scope="selected"]');
    var selectedLabel = selectedButton ? selectedButton.querySelector('span') : null;
    if (status) status.textContent = message || (selected.length + (selected.length === 1 ? ' row selected' : ' rows selected'));
    if (allLabel) allLabel.textContent = 'Export all (' + rows.length + ')';
    if (selectedLabel) selectedLabel.textContent = 'Export selected (' + selected.length + ')';
    if (selectedButton) selectedButton.disabled = selected.length === 0;
  }

  function cleanCell(cell) {
    if (!cell) return '';
    var clone = cell.cloneNode(true);
    var sourceControls = cell.querySelectorAll('input, select, textarea');
    var clonedControls = clone.querySelectorAll('input, select, textarea');
    Array.prototype.forEach.call(clonedControls, function (control, index) {
      var source = sourceControls[index];
      var value = '';
      if (source && !source.matches('[data-pms-select]')) {
        if (source.tagName === 'SELECT') {
          value = Array.prototype.filter.call(source.options, function (option) { return option.selected; })
            .map(function (option) { return option.textContent.trim(); }).join(' | ');
        } else if ((source.type === 'checkbox' || source.type === 'radio')) {
          value = source.checked ? (source.value || 'Yes') : '';
        } else {
          value = source.value || '';
        }
      }
      control.replaceWith(document.createTextNode(value));
    });
    Array.prototype.forEach.call(clone.querySelectorAll(
      '.pms-table-select, .pms-no-export, [data-pms-export="off"], script, style, button, [aria-hidden="true"]'
    ), function (node) { node.remove(); });
    return (clone.innerText || clone.textContent || '')
      .replace(/\u00a0/g, ' ')
      .replace(/[ \t]+/g, ' ')
      .replace(/\s*\n\s*/g, ' | ')
      .trim();
  }

  function exportColumns(table) {
    var row = headerRow(table);
    if (!row) return [];
    return Array.prototype.map.call(row.cells, function (cell, index) {
      var label = cleanCell(cell).replace(/:\s*$/, '').trim();
      var excluded = !label || cell.matches('.no-export, .pms-no-export, [data-pms-export="off"]') || actionHeaderPattern.test(label);
      return { index: index, label: label || ('Column ' + (index + 1)), excluded: excluded };
    }).filter(function (column) { return !column.excluded; });
  }

  function safeCsvValue(value) {
    var text = String(value == null ? '' : value);
    if (/^[=+\-@]/.test(text)) text = "'" + text;
    return '"' + text.replace(/"/g, '""') + '"';
  }

  function tableName(controller) {
    var table = controller.table;
    var caption = table.caption ? cleanCell(table.caption) : '';
    var nearbyHeading = '';
    var section = table.closest('.card, .modal-content, section, main, .container, .container-fluid, .container-xxl');
    if (section) {
      var heading = section.querySelector('h1, h2, h3, h4, .card-title, .modal-title');
      if (heading) nearbyHeading = cleanCell(heading);
    }
    var name = table.getAttribute('aria-label') || caption || nearbyHeading || document.title || 'admin-table';
    name = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '').slice(0, 70);
    return name || ('admin-table-' + controller.number);
  }

  function downloadCsv(controller, scope) {
    var columns = exportColumns(controller.table);
    var availableRows = dataRows(controller.table);
    var rows = scope === 'selected'
      ? availableRows.filter(function (row) { return controller.selectedRows.has(row); })
      : availableRows;

    if (!rows.length || !columns.length) {
      syncToolbar(controller, scope === 'selected' ? 'Select at least one row to export' : 'No rows available to export');
      return;
    }

    var csvRows = [columns.map(function (column) { return safeCsvValue(column.label); }).join(',')];
    rows.forEach(function (row) {
      csvRows.push(columns.map(function (column) {
        return safeCsvValue(cleanCell(row.cells[column.index]));
      }).join(','));
    });

    var blob = new Blob(['\ufeff' + csvRows.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
    var url = URL.createObjectURL(blob);
    var link = document.createElement('a');
    var date = new Date();
    var dateStamp = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
    link.href = url;
    link.download = tableName(controller) + '-' + (scope === 'selected' ? 'selected' : 'all') + '-' + dateStamp + '.csv';
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
    syncToolbar(controller, 'Exported ' + rows.length + (rows.length === 1 ? ' row' : ' rows'));
  }

  function closeMenu(controller) {
    if (!controller.toolbar) return;
    var exportBox = controller.toolbar.querySelector('.pms-table-export');
    var toggle = controller.toolbar.querySelector('.pms-table-export__toggle');
    if (exportBox) exportBox.classList.remove('is-open');
    if (toggle) toggle.setAttribute('aria-expanded', 'false');
  }

  function scheduleSync(controller) {
    if (controller.syncQueued) return;
    controller.syncQueued = true;
    window.requestAnimationFrame(function () {
      controller.syncQueued = false;
      syncRows(controller);
    });
  }

  function bindController(controller) {
    controller.table.addEventListener('click', function (event) {
      if (event.target.closest('.pms-table-select')) event.stopPropagation();
    });

    controller.table.addEventListener('change', function (event) {
      var input = event.target.closest('[data-pms-select]');
      if (!input || input.getAttribute('data-pms-table') !== controller.id) return;

      if (input.getAttribute('data-pms-select') === 'all') {
        dataRows(controller.table).forEach(function (row) {
          var rowCheckbox = row.querySelector('[data-pms-select="row"][data-pms-table="' + controller.id + '"]');
          if (input.checked) controller.selectedRows.add(row);
          else controller.selectedRows.delete(row);
          if (rowCheckbox) rowCheckbox.checked = input.checked;
          row.classList.toggle('pms-row-selected', input.checked);
        });
      } else {
        var selectedRow = input.closest('tr');
        if (selectedRow) {
          if (input.checked) controller.selectedRows.add(selectedRow);
          else controller.selectedRows.delete(selectedRow);
          selectedRow.classList.toggle('pms-row-selected', input.checked);
        }
      }
      syncToolbar(controller);
    });

    controller.toolbar.addEventListener('click', function (event) {
      var toggle = event.target.closest('.pms-table-export__toggle');
      if (toggle) {
        var exportBox = toggle.closest('.pms-table-export');
        var isOpen = !exportBox.classList.contains('is-open');
        document.querySelectorAll('.pms-table-export.is-open').forEach(function (openMenu) {
          openMenu.classList.remove('is-open');
          var openToggle = openMenu.querySelector('.pms-table-export__toggle');
          if (openToggle) openToggle.setAttribute('aria-expanded', 'false');
        });
        exportBox.classList.toggle('is-open', isOpen);
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        return;
      }

      var exportButton = event.target.closest('[data-export-scope]');
      if (exportButton && !exportButton.disabled) {
        downloadCsv(controller, exportButton.getAttribute('data-export-scope'));
        closeMenu(controller);
      }
    });

    controller.observer = new MutationObserver(function (mutations) {
      var rowsChanged = mutations.some(function (mutation) {
        return mutation.type === 'childList' && (mutation.addedNodes.length || mutation.removedNodes.length);
      });
      if (rowsChanged) scheduleSync(controller);
    });
    controller.observer.observe(controller.table, { childList: true, subtree: true });

    if (window.jQuery) {
      window.jQuery(controller.table).on('draw.dt.pmsTableTools', function () {
        scheduleSync(controller);
      });
    }
  }

  function enhance(table) {
    if (!isEligible(table) || controllers.has(table)) return;
    controllerNumber += 1;
    var controller = {
      id: 'pms-table-' + controllerNumber,
      number: controllerNumber,
      table: table,
      toolbar: null,
      selectAll: null,
      selectedRows: new Set(),
      placeholderRows: new WeakSet(),
      syncQueued: false,
      observer: null,
      scrollHost: null,
      legacySelectionColumn: hasLegacySelectionColumn(table)
    };
    controllers.set(table, controller);
    table.classList.add('pms-exportable-table');
    table.setAttribute('data-pms-table-tools-id', controller.id);
    createToolbar(controller);
    addHeaderCheckbox(controller);
    syncRows(controller);
    bindController(controller);
  }

  function isCardCandidate(element) {
    if (!element || element.nodeType !== 1 || element.matches('[data-pms-card-border="off"]')) return false;
    if (!element.matches('div, section, article, aside, li, a, form')) return false;
    if (Array.prototype.some.call(element.classList, function (className) { return /^fa[srlbd]?(-|$)|^bx(-|$)/.test(className); })) return false;
    if (element.classList.contains('card')) return true;

    return Array.prototype.some.call(element.classList, function (className) {
      if (className.endsWith('-card')) return true;
      if (className.indexOf('-card-') === -1) return false;
      return !/-card-(body|content|footer|header|icon|image|img|left|right|title|subtitle|text|meta|actions?|wrapper|section|grid|row|col)(-|$)/.test(className);
    });
  }

  function enhanceCard(card) {
    if (!isCardCandidate(card) || card.getAttribute('data-pms-card-border-ready') === 'true') return;
    cardNumber += 1;

    var hue = Math.round((cardNumber * 137.508) % 360);
    var secondHue = Math.round((hue + 52) % 360);
    var layer = document.createElement('span');
    layer.className = 'pms-card-border-layer';
    layer.setAttribute('aria-hidden', 'true');

    card.setAttribute('data-pms-card-border-ready', 'true');
    card.classList.add('pms-animated-card-border');
    if (window.getComputedStyle(card).position === 'static') card.classList.add('pms-card-needs-position');
    card.style.setProperty('--pms-card-color-a', 'hsl(' + hue + ' 84% 52%)');
    card.style.setProperty('--pms-card-color-b', 'hsl(' + secondHue + ' 88% 58%)');
    var borderDuration = 9.5 + (cardNumber % 6) * 0.65;
    card.style.setProperty('--pms-card-border-duration', borderDuration.toFixed(2) + 's');
    card.style.setProperty('--pms-card-border-hover-duration', (borderDuration * 0.58).toFixed(2) + 's');
    card.style.setProperty('--pms-card-border-delay', (-cardNumber * 0.31).toFixed(2) + 's');
    card.insertBefore(layer, card.firstChild);
  }

  function scanCards(root) {
    if (!root) return;
    if (root.matches && root.matches(cardSelector)) enhanceCard(root);
    if (root.querySelectorAll) root.querySelectorAll(cardSelector).forEach(enhanceCard);
  }

  function scan(root) {
    if (!root) return;
    if (root.matches && root.matches('table')) enhance(root);
    if (root.querySelectorAll) root.querySelectorAll('table').forEach(enhance);
    scanCards(root);
  }

  function start() {
    scan(document);

    var pendingScanRoots = new Set();
    var scanQueued = false;
    function schedulePageScan(node) {
      pendingScanRoots.add(node);
      if (scanQueued) return;
      scanQueued = true;
      window.requestAnimationFrame(function () {
        scanQueued = false;
        pendingScanRoots.forEach(function (root) { scan(root); });
        pendingScanRoots.clear();
      });
    }

    var pageObserver = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        Array.prototype.forEach.call(mutation.addedNodes, function (node) {
          if (node.nodeType === 1) schedulePageScan(node);
        });
      });
    });
    pageObserver.observe(document.body, { childList: true, subtree: true });

    document.addEventListener('click', function (event) {
      if (event.target.closest('.pms-table-export')) return;
      document.querySelectorAll('.pms-table-export.is-open').forEach(function (menu) {
        menu.classList.remove('is-open');
        var toggle = menu.querySelector('.pms-table-export__toggle');
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
      });
    });

    document.addEventListener('keydown', function (event) {
      if (event.key !== 'Escape') return;
      document.querySelectorAll('.pms-table-export.is-open').forEach(function (menu) {
        menu.classList.remove('is-open');
        var toggle = menu.querySelector('.pms-table-export__toggle');
        if (toggle) {
          toggle.setAttribute('aria-expanded', 'false');
          toggle.focus();
        }
      });
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
})();
