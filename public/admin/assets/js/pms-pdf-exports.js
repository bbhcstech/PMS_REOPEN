(function (window) {
  'use strict';
  // Shared table PDF defaults; row selection and column filters stay in each page.
  function styleDocument(doc) {
    doc.pageMargins = [32, 40, 32, 42];
    doc.defaultStyle = Object.assign({}, doc.defaultStyle, { font: 'Roboto', fontSize: 9, color: '#1e293b' });
    doc.styles = Object.assign({}, doc.styles, {
      title: { fontSize: 17, bold: true, color: '#1e3a8a', alignment: 'left', margin: [0, 0, 0, 12] },
      tableHeader: { bold: true, fontSize: 9, color: '#ffffff', fillColor: '#1e3a8a' },
      tableBodyEven: { fillColor: '#ffffff' },
      tableBodyOdd: { fillColor: '#f1f5f9' }
    });
    (doc.content || []).forEach(function (item) {
      if (!item.table) return;
      item.table.headerRows = item.table.headerRows || 1;
      item.table.keepWithHeaderRows = 1;
      item.table.widths = Array(item.table.body[0].length).fill('*');
      item.layout = {
        hLineWidth: function () { return 0.5; }, vLineWidth: function () { return 0.5; },
        hLineColor: function () { return '#cbd5e1'; }, vLineColor: function () { return '#cbd5e1'; },
        paddingLeft: function () { return 5; }, paddingRight: function () { return 5; },
        paddingTop: function () { return 5; }, paddingBottom: function () { return 5; }
      };
    });
    var generated = new Date().toLocaleDateString();
    doc.footer = function (page, pages) {
      return { margin: [32, 12, 32, 0], fontSize: 8, color: '#64748b', columns: [
        { text: 'Generated ' + generated }, { text: 'Page ' + page + ' of ' + pages, alignment: 'right' }
      ] };
    };
  }
  function fromTable(table, title, subtitle, meta, orientation) {
    var rows = Array.from(table.rows).filter(function (row) { return row.cells.length; });
    var body = rows.map(function (row, index) {
      return Array.from(row.cells).map(function (cell) {
        var text = (cell.innerText || cell.textContent || '').replace(/\s+/g, ' ').trim();
        if (index === 0) return { text: text, style: 'tableHeader' };
        var image = cell.querySelector('img');
        if (image && image.complete && image.naturalWidth) {
          try {
            var canvas = document.createElement('canvas');
            var scale = Math.min(64 / image.naturalWidth, 64 / image.naturalHeight);
            canvas.width = Math.max(1, Math.round(image.naturalWidth * scale));
            canvas.height = Math.max(1, Math.round(image.naturalHeight * scale));
            canvas.getContext('2d').drawImage(image, 0, 0, canvas.width, canvas.height);
            return { columns: [{ image: canvas.toDataURL('image/png'), fit: [20, 20], width: 25 }, { text: text, width: '*' }] };
          } catch (error) { /* A cross-origin logo retains its company text. */ }
        }
        return text;
      });
    });
    // HTML empty states use colspan; pdfmake requires one cell for each column.
    var columns = body[0] ? body[0].length : 0;
    body.forEach(function (row) { while (row.length < columns) row.push(''); });
    var doc = { pageSize: 'A4', pageOrientation: orientation || 'portrait', content: [
      { text: title, style: 'title' },
      { text: subtitle || '', color: '#64748b', margin: [0, 0, 0, 5] },
      { text: meta || '', fontSize: 8, color: '#64748b', margin: [0, 0, 0, 12] },
      { table: { headerRows: 1, body: body } }
    ] };
    styleDocument(doc);
    return doc;
  }
  window.PmsPdfExports = { styleDocument: styleDocument, fromTable: fromTable };
  // Some pages reload DataTables Buttons after the layout scripts. Apply defaults
  // at PDF creation so those exports receive the same typography and pagination.
  if (window.pdfMake) {
    var createPdf = window.pdfMake.createPdf;
    window.pdfMake.createPdf = function (doc) {
      styleDocument(doc);
      return createPdf.apply(this, arguments);
    };
  }
})(window);
