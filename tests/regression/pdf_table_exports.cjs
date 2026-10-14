// Exercise the shipped browser renderer and font bundle, without CDN/network access.
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const root = path.resolve(__dirname, '../..');
const context = { console, setTimeout, clearTimeout, setImmediate, clearImmediate, Buffer, TextEncoder, TextDecoder, Uint8Array, ArrayBuffer };
context.window = context;
context.self = context;
context.navigator = { userAgent: 'PDF integration test' };
context.jQuery = { fn: { dataTable: { ext: { buttons: { pdfHtml5: {} } } } } };
vm.createContext(context);
for (const file of ['public/admin/assets/vendor/pdfmake/pdfmake.min.js', 'public/admin/assets/vendor/pdfmake/vfs_fonts.js', 'public/admin/assets/js/pms-pdf-exports.js']) {
  vm.runInContext(fs.readFileSync(path.join(root, file), 'utf8'), context, { filename: file });
}
const body = [[{ text: 'Employee', style: 'tableHeader' }, { text: 'Salary', style: 'tableHeader' }, { text: 'Status', style: 'tableHeader' }]];
for (let i = 1; i <= 220; i++) body.push(['José Employee ' + i, '₹50,000.00', 'Active']);
const doc = { pageSize: 'A4', content: [{ text: 'Employee Report', style: 'title' }, { table: { body } }] };
const cell = text => ({ textContent: text, querySelector: () => null });
const tableDocument = context.PmsPdfExports.fromTable({ rows: [
  { cells: [cell('Company'), cell('Plan')] },
  { cells: [cell('José Company'), cell('₹5,000')] },
  { cells: [cell('No other records')] }
] }, 'Companies', 'Platform export', 'Generated today', 'landscape');
assert.equal(tableDocument.pageOrientation, 'landscape');
assert.equal(tableDocument.content[3].table.body[1][1], '₹5,000');
assert.equal(tableDocument.content[3].table.body[2].length, 2);
const pdf = context.pdfMake.createPdf(doc);
assert.equal(doc.content[1].table.headerRows, 1);
assert.equal(doc.defaultStyle.font, 'Roboto');
assert.equal(doc.footer(3, 7).columns[1].text, 'Page 3 of 7');
assert.equal(body.length, 221, 'Styling must not alter selected export data');
pdf.getBuffer(buffer => {
  assert.equal(Buffer.from(buffer).subarray(0, 4).toString(), '%PDF');
  const artifact = path.join(root, 'storage/app/pdf/regression/browser-table.pdf');
  fs.mkdirSync(path.dirname(artifact), { recursive: true });
  fs.writeFileSync(artifact, buffer);
  console.log('PASS: local PDFMake/fonts, Unicode table, shared styling and page fields');
});
