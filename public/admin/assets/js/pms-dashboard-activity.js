(function () {
'use strict';
    // Activity Logs Table Controls (Show entries: 10, 20, 30, search, pagination, CSV export)
    let activityPageSize = 10;
    let activityCurrentPage = 1;

    function renderActivityTable() {
      const allRows = Array.from(document.querySelectorAll('#activityLogsTableBody tr.activity-log-row'));
      const emptyRow = document.querySelector('#activityLogsTableBody tr.no-activity-row');
      const query = (document.getElementById('activityTableSearch')?.value || '').toLowerCase().trim();

      const matchedRows = allRows.filter(row => {
        if (!query) return true;
        return row.textContent.toLowerCase().includes(query);
      });

      const totalMatched = matchedRows.length;
      const totalPages = Math.ceil(totalMatched / activityPageSize) || 1;

      if (activityCurrentPage > totalPages) activityCurrentPage = totalPages;
      if (activityCurrentPage < 1) activityCurrentPage = 1;

      const startIndex = (activityCurrentPage - 1) * activityPageSize;
      const endIndex = Math.min(startIndex + activityPageSize, totalMatched);

      allRows.forEach(row => row.style.display = 'none');

      for (let i = startIndex; i < endIndex; i++) {
        if (matchedRows[i]) {
          matchedRows[i].style.display = '';
        }
      }

      if (emptyRow) {
        emptyRow.style.display = totalMatched === 0 ? '' : 'none';
      }

      const showingText = document.getElementById('activityShowingText');
      if (showingText) {
        if (totalMatched === 0) {
          showingText.innerHTML = 'Showing 0 to 0 of 0 entries';
        } else {
          showingText.innerHTML = `Showing ${startIndex + 1} to ${endIndex} of ${totalMatched} entries`;
        }
      }

      renderActivityPagination(totalPages);
    }

    function renderActivityPagination(totalPages) {
      const container = document.getElementById('activityPaginationControls');
      if (!container) return;

      container.innerHTML = '';
      if (totalPages <= 1) return;

      const prevBtn = document.createElement('button');
      prevBtn.type = 'button';
      prevBtn.className = 'btn-chart';
      prevBtn.innerHTML = '<i class="bx bx-chevron-left"></i>';
      prevBtn.style.padding = '4px 8px';
      prevBtn.style.borderRadius = '6px';
      prevBtn.style.cursor = activityCurrentPage === 1 ? 'not-allowed' : 'pointer';
      prevBtn.style.opacity = activityCurrentPage === 1 ? '0.5' : '1';
      prevBtn.disabled = activityCurrentPage === 1;
      prevBtn.onclick = () => {
        if (activityCurrentPage > 1) {
          activityCurrentPage--;
          renderActivityTable();
        }
      };
      container.appendChild(prevBtn);

      let startP = Math.max(1, activityCurrentPage - 2);
      let endP = Math.min(totalPages, startP + 4);
      if (endP - startP < 4) {
        startP = Math.max(1, endP - 4);
      }

      for (let p = startP; p <= endP; p++) {
        const pageBtn = document.createElement('button');
        pageBtn.type = 'button';
        pageBtn.className = 'btn-chart' + (p === activityCurrentPage ? ' active' : '');
        pageBtn.textContent = p;
        pageBtn.style.padding = '4px 10px';
        pageBtn.style.borderRadius = '6px';
        pageBtn.style.fontWeight = '700';
        pageBtn.style.cursor = 'pointer';
        const targetP = p;
        pageBtn.onclick = () => {
          activityCurrentPage = targetP;
          renderActivityTable();
        };
        container.appendChild(pageBtn);
      }

      const nextBtn = document.createElement('button');
      nextBtn.type = 'button';
      nextBtn.className = 'btn-chart';
      nextBtn.innerHTML = '<i class="bx bx-chevron-right"></i>';
      nextBtn.style.padding = '4px 8px';
      nextBtn.style.borderRadius = '6px';
      nextBtn.style.cursor = activityCurrentPage === totalPages ? 'not-allowed' : 'pointer';
      nextBtn.style.opacity = activityCurrentPage === totalPages ? '0.5' : '1';
      nextBtn.disabled = activityCurrentPage === totalPages;
      nextBtn.onclick = () => {
        if (activityCurrentPage < totalPages) {
          activityCurrentPage++;
          renderActivityTable();
        }
      };
      container.appendChild(nextBtn);
    }

    function changeActivityEntriesPerPage(val) {
      activityPageSize = parseInt(val) || 10;
      activityCurrentPage = 1;
      renderActivityTable();
    }

    function filterActivityLogs() {
      activityCurrentPage = 1;
      renderActivityTable();
    }

    function exportActivityLogsToCSV() {
      const allRows = Array.from(document.querySelectorAll('#activityLogsTableBody tr.activity-log-row'));
      const query = (document.getElementById('activityTableSearch')?.value || '').toLowerCase().trim();
      const exportRows = allRows.filter(row => !query || row.textContent.toLowerCase().includes(query));
      if (exportRows.length === 0) {
        alert('No activity log entries to export.');
        return;
      }

      const headers = ['Timestamp', 'Company', 'Action', 'IP Address', 'Status'];
      const csvRows = [headers.join(',')];

      exportRows.forEach(row => {
        const cols = row.querySelectorAll('td:not(.pms-table-selection-cell)');
        if (cols.length >= 5) {
          const timestamp = '"' + (cols[0].textContent || '').replace(/"/g, '""').trim() + '"';
          const company = '"' + (cols[1].textContent || '').replace(/"/g, '""').trim() + '"';
          const action = '"' + (cols[2].textContent || '').replace(/"/g, '""').trim() + '"';
          const ip = '"' + (cols[3].textContent || '').replace(/"/g, '""').trim() + '"';
          const status = '"' + (cols[4].textContent || '').replace(/"/g, '""').trim() + '"';
          csvRows.push([timestamp, company, action, ip, status].join(','));
        }
      });

      const csvContent = '\uFEFF' + csvRows.join('\r\n');
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.setAttribute('href', url);
      link.setAttribute('download', 'activity_logs_export_' + new Date().toISOString().slice(0, 10) + '.csv');
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      setTimeout(() => URL.revokeObjectURL(url), 1000);
    }


    document.addEventListener('input', event => {
      if (event.target.id === 'activityTableSearch') filterActivityLogs();
    });
    document.addEventListener('change', event => {
      if (event.target.id === 'activityEntriesPerPageSelect') changeActivityEntriesPerPage(event.target.value);
    });
    document.addEventListener('click', event => {
      if (event.target.closest('[data-activity-export]')) {
        event.preventDefault();
        exportActivityLogsToCSV();
      }
    });
    document.addEventListener('pms:records-updated', renderActivityTable);
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', renderActivityTable);
    else renderActivityTable();
})();
