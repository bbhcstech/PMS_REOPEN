/**
 * Recruitment Specification Sheet (single A4 page).
 *
 *  - pdfkit  : page layout and typography (title, job card, details grid, sections, policy box),
 *              auto-fitting the content into the space between the company header and footer.
 *  - pdf-lib : places the company header image at the top and the footer image at the bottom,
 *              enforces a single page and writes the document metadata.
 *
 * Input : JSON on stdin (built by App\Services\RecruitmentPdfRenderer).
 * Output: PDF bytes on stdout. Errors go to stderr with a non-zero exit code.
 */
import PDFKit from 'pdfkit';
import { PDFDocument } from 'pdf-lib';

const A4 = { width: 595.28, height: 841.89 };
const MARGIN_X = 42.52; // 15 mm
const EDGE = 22.68; // 8 mm from the paper edge to the header/footer artwork
const CONTENT_W = A4.width - MARGIN_X * 2;
const C = {
    violet: '#6D28D9', violetDark: '#3B0764', violetSoft: '#F5F3FF', violetLine: '#DDD6FE', accent: '#7C3AED',
    ink: '#111827', body: '#374151', muted: '#6B7280', faint: '#9CA3AF', line: '#E5E7EB', cell: '#F9FAFB',
    open: ['#047857', '#D1FAE5'], progress: ['#B45309', '#FEF3C7'], closed: ['#374151', '#E5E7EB'], cancelled: ['#B91C1C', '#FEE2E2'],
};

const readStdin = async () => {
    const chunks = [];
    for await (const chunk of process.stdin) chunks.push(chunk);
    return JSON.parse(Buffer.concat(chunks).toString('utf8'));
};

const imageFrom = (image) => (image && image.base64 ? { mime: String(image.mime || ''), bytes: Buffer.from(image.base64, 'base64') } : null);

/** Lays out the body with pdfkit. With draw=false it only measures and returns the bottom y. */
function layoutBody(doc, data, box, scale, draw) {
    const s = (size) => size * scale;
    let y = box.top;

    // 1. Document title row
    const refText = `Ref: ${data.doc.ref}`;
    const issuedText = `Issued: ${data.doc.issued}`;
    doc.font('Bold').fontSize(s(15));
    if (draw) doc.fillColor(C.violet).text('RECRUITMENT SPECIFICATION SHEET', MARGIN_X, y, { width: CONTENT_W * 0.7, characterSpacing: 0.4, lineBreak: false });
    doc.font('Regular').fontSize(s(8));
    if (draw) {
        doc.fillColor(C.muted).text(refText, MARGIN_X, y + s(1), { width: CONTENT_W, align: 'right' });
        doc.text(issuedText, MARGIN_X, y + s(12), { width: CONTENT_W, align: 'right' });
    }
    y += s(20);
    doc.font('Regular').fontSize(s(8.5));
    if (draw) doc.fillColor(C.muted).text('Official job requirement — for personal and professional sharing', MARGIN_X, y, { width: CONTENT_W * 0.7 });
    y += s(14);
    if (draw) doc.save().moveTo(MARGIN_X, y).lineTo(MARGIN_X + CONTENT_W, y).lineWidth(2).strokeColor(C.accent).stroke().restore();
    y += s(12);

    // 2. Job card
    const cardPad = s(12);
    doc.font('Bold').fontSize(s(17));
    const titleH = doc.heightOfString(data.job.title, { width: CONTENT_W - cardPad * 2 - 6 });
    const cardH = cardPad + titleH + s(6) + s(16) + cardPad;
    if (draw) {
        doc.save().roundedRect(MARGIN_X, y, CONTENT_W, cardH, 6).fill(C.violetSoft).restore();
        doc.save().rect(MARGIN_X, y, 4, cardH).fill(C.accent).restore();
        doc.fillColor(C.violetDark).text(data.job.title, MARGIN_X + cardPad + 6, y + cardPad, { width: CONTENT_W - cardPad * 2 - 6 });
        let chipX = MARGIN_X + cardPad + 6;
        const chipY = y + cardPad + titleH + s(6);
        doc.font('Bold').fontSize(s(8));
        for (const [label, value] of [['Department', data.job.department], ['Employment', data.job.employmentType]]) {
            const text = `${label}: ${value}`;
            const w = doc.widthOfString(text) + s(14);
            doc.save().roundedRect(chipX, chipY, w, s(15), s(7.5)).fill('#FFFFFF').restore();
            doc.save().roundedRect(chipX, chipY, w, s(15), s(7.5)).lineWidth(0.6).strokeColor(C.violetLine).stroke().restore();
            doc.fillColor(C.violet).text(text, chipX + s(7), chipY + s(3.6), { lineBreak: false });
            chipX += w + s(6);
        }
    }
    y += cardH + s(12);

    // 3. Key details grid (3 columns x 2 rows)
    const facts = [
        ['Vacancies', data.job.positions], ['Experience Required', data.job.experience], ['Salary Range (CTC)', data.job.salary],
        ['Work Location', data.job.location], ['Status', data.job.status.label], ['Posted By', data.job.postedBy],
    ];
    const colW = CONTENT_W / 3;
    const rowH = s(38);
    if (draw) {
        doc.save().roundedRect(MARGIN_X, y, CONTENT_W, rowH * 2, 6).fill(C.cell).restore();
        doc.save().roundedRect(MARGIN_X, y, CONTENT_W, rowH * 2, 6).lineWidth(0.7).strokeColor(C.line).stroke().restore();
        doc.save().lineWidth(0.7).strokeColor(C.line);
        doc.moveTo(MARGIN_X, y + rowH).lineTo(MARGIN_X + CONTENT_W, y + rowH).stroke();
        doc.moveTo(MARGIN_X + colW, y).lineTo(MARGIN_X + colW, y + rowH * 2).stroke();
        doc.moveTo(MARGIN_X + colW * 2, y).lineTo(MARGIN_X + colW * 2, y + rowH * 2).stroke();
        doc.restore();
        facts.forEach(([label, value], index) => {
            const cx = MARGIN_X + (index % 3) * colW + s(11);
            const cy = y + Math.floor(index / 3) * rowH + s(8);
            doc.font('Bold').fontSize(s(6.8)).fillColor(C.muted).text(label.toUpperCase(), cx, cy, { width: colW - s(22), characterSpacing: 0.5, lineBreak: false });
            if (label === 'Status') {
                const [fg, bg] = C[data.job.status.tone] || C.closed;
                doc.font('Bold').fontSize(s(8.5));
                const w = doc.widthOfString(String(value)) + s(14);
                doc.save().roundedRect(cx, cy + s(11), w, s(15), s(7.5)).fill(bg).restore();
                doc.fillColor(fg).text(String(value), cx + s(7), cy + s(14.5), { lineBreak: false });
            } else {
                doc.font('Bold').fontSize(s(10)).fillColor(C.ink).text(String(value), cx, cy + s(12), { width: colW - s(22), height: s(14), ellipsis: true, lineBreak: false });
            }
        });
    }
    y += rowH * 2 + s(14);

    // 4. Text sections (description, qualifications) — share the remaining space, clipped with an ellipsis.
    const policyH = measurePolicy(doc, data, scale);
    const sections = [['Job Description & Responsibilities', data.description], ['Candidate Qualifications & Skills', data.qualifications]]
        .filter(([, text]) => text);
    const available = Math.max(0, box.bottom - y - policyH - s(12) - sections.length * s(22));
    doc.font('Regular').fontSize(s(9.5));
    const wanted = sections.map(([, text]) => doc.heightOfString(text, { width: CONTENT_W, lineGap: s(2) }));
    const total = wanted.reduce((a, b) => a + b, 0);
    const allowed = wanted.map((h) => (total <= available ? h : Math.max(s(24), available * (h / total))));
    sections.forEach(([heading, text], index) => {
        if (draw) {
            doc.save().rect(MARGIN_X, y + s(2), 3, s(9)).fill(C.accent).restore();
            doc.font('Bold').fontSize(s(9)).fillColor(C.violet).text(heading.toUpperCase(), MARGIN_X + 9, y, { width: CONTENT_W - 9, characterSpacing: 0.5, lineBreak: false });
        }
        y += s(16);
        if (draw) doc.font('Regular').fontSize(s(9.5)).fillColor(C.body).text(text, MARGIN_X, y, { width: CONTENT_W, lineGap: s(2), height: allowed[index], ellipsis: true });
        y += Math.min(wanted[index], allowed[index]) + s(6);
    });

    // 5. Recruitment policy box
    y += s(6);
    if (draw) drawPolicy(doc, data, scale, y, policyH);
    y += policyH;
    return { bottom: y, overflow: total > available };
}

function policyLines(data) {
    return [
        ['Probation Period', data.policy.probation],
        ['Hiring SLA Target', data.policy.sla],
        ['Pipeline Stages', data.policy.pipeline],
        ['Allowed Resume Formats', data.policy.resume],
        ['Equal Opportunity', data.policy.equalOpportunity],
    ].filter(([, text]) => text);
}

function measurePolicy(doc, data, scale) {
    const s = (size) => size * scale;
    let h = s(12) + s(16);
    for (const [label, text] of policyLines(data)) {
        doc.font('Regular').fontSize(s(8.3));
        h += doc.heightOfString(`${label}: ${text}`, { width: CONTENT_W - s(36), lineGap: s(1.2) }) + s(3);
    }
    return h + s(9);
}

function drawPolicy(doc, data, scale, y, height) {
    const s = (size) => size * scale;
    doc.save().roundedRect(MARGIN_X, y, CONTENT_W, height, 6).fill('#FAF5FF').restore();
    doc.save().roundedRect(MARGIN_X, y, CONTENT_W, height, 6).lineWidth(0.7).strokeColor(C.violetLine).stroke().restore();
    doc.font('Bold').fontSize(s(9.2)).fillColor(C.violet).text(`Company Recruitment Policy Guidelines (${data.policy.code})`, MARGIN_X + s(14), y + s(10), { width: CONTENT_W - s(28), lineBreak: false });
    let ly = y + s(12) + s(16);
    for (const [label, text] of policyLines(data)) {
        doc.save().circle(MARGIN_X + s(18), ly + s(4.3), s(1.8)).fill(C.accent).restore();
        doc.font('Bold').fontSize(s(8.3)).fillColor(C.violetDark).text(`${label}: `, MARGIN_X + s(26), ly, { continued: true, width: CONTENT_W - s(36), lineGap: s(1.2) });
        doc.font('Regular').fillColor('#4C1D95').text(text, { width: CONTENT_W - s(36), lineGap: s(1.2) });
        doc.font('Regular').fontSize(s(8.3));
        ly += doc.heightOfString(`${label}: ${text}`, { width: CONTENT_W - s(36), lineGap: s(1.2) }) + s(3);
    }
}

async function buildBody(data, frame) {
    const doc = new PDFKit({ size: 'A4', margin: 0, autoFirstPage: true, compress: true });
    doc.registerFont('Regular', data.fonts.regular);
    doc.registerFont('Bold', data.fonts.bold);
    const done = new Promise((resolve, reject) => {
        const chunks = [];
        doc.on('data', (chunk) => chunks.push(chunk));
        doc.on('end', () => resolve(Buffer.concat(chunks)));
        doc.on('error', reject);
    });

    // Pick the largest type scale whose layout fits between the header and the footer.
    const box = { top: frame.bodyTop, bottom: frame.bodyBottom };
    let scale = 1;
    for (const candidate of [1, 0.95, 0.9, 0.86, 0.82, 0.78]) {
        scale = candidate;
        const result = layoutBody(doc, data, box, candidate, false);
        if (result.bottom <= box.bottom && !result.overflow) break;
    }
    layoutBody(doc, data, box, scale, true);

    // Branded fallback band when the company has no header artwork.
    if (!frame.hasHeader) {
        doc.save().rect(0, 0, A4.width, 6).fill(C.accent).restore();
        doc.font('Bold').fontSize(11).fillColor(C.violetDark).text(data.meta.company, MARGIN_X, EDGE, { width: CONTENT_W * 0.6, lineBreak: false });
        doc.font('Regular').fontSize(8).fillColor(C.muted).text('Talent Acquisition', MARGIN_X, EDGE + 14, { lineBreak: false });
        doc.save().moveTo(MARGIN_X, frame.bodyTop - 10).lineTo(MARGIN_X + CONTENT_W, frame.bodyTop - 10).lineWidth(0.6).strokeColor(C.line).stroke().restore();
    }

    // Confidentiality line just above the footer artwork (or the page bottom).
    const noteY = frame.bodyBottom + 8;
    doc.save().moveTo(MARGIN_X, noteY).lineTo(MARGIN_X + CONTENT_W, noteY).lineWidth(0.5).strokeColor(C.line).stroke().restore();
    doc.font('Regular').fontSize(7).fillColor(C.faint)
        .text(`Confidential — Corporate recruitment document  •  Generated ${data.doc.generated}  •  For employee, manager and HR sharing`, MARGIN_X, noteY + 5, { width: CONTENT_W, align: 'center', lineBreak: false });

    doc.end();
    return done;
}

/** Header/footer artwork sizes (180 mm wide, aspect ratio kept) and the body frame between them. */
function frameFor(header, footer, measure) {
    const size = (image, maxH) => {
        if (!image) return null;
        const info = measure(image.bytes);
        const width = CONTENT_W;
        const height = Math.min(maxH, width * (info.height / info.width));
        return { width: height < width * (info.height / info.width) ? height * (info.width / info.height) : width, height };
    };
    const head = size(header, 90);
    const foot = size(footer, 64);
    const headerBottom = head ? EDGE + head.height : EDGE + 26;
    const footerTop = foot ? A4.height - EDGE - foot.height : A4.height - EDGE;
    return {
        hasHeader: Boolean(head), head, foot,
        bodyTop: headerBottom + 18,
        bodyBottom: footerTop - 26, // leaves room for the confidentiality line
    };
}

async function main() {
    const data = await readStdin();
    const header = imageFrom(data.images?.header);
    const footer = imageFrom(data.images?.footer);

    const probe = new PDFKit({ autoFirstPage: false });
    const frame = frameFor(header, footer, (bytes) => probe.openImage(bytes));

    const body = await buildBody(data, frame);

    // pdf-lib: brand artwork in its fixed positions, single page, document metadata.
    const pdf = await PDFDocument.load(body);
    while (pdf.getPageCount() > 1) pdf.removePage(pdf.getPageCount() - 1);
    const page = pdf.getPage(0);
    const embed = (image) => (/png/i.test(image.mime) ? pdf.embedPng(image.bytes) : pdf.embedJpg(image.bytes));
    if (header) {
        const art = await embed(header);
        page.drawImage(art, { x: (A4.width - frame.head.width) / 2, y: A4.height - EDGE - frame.head.height, width: frame.head.width, height: frame.head.height });
    }
    if (footer) {
        const art = await embed(footer);
        page.drawImage(art, { x: (A4.width - frame.foot.width) / 2, y: EDGE, width: frame.foot.width, height: frame.foot.height });
    }
    pdf.setTitle(`Job Requirement — ${data.job.title}`, { showInWindowTitleBar: true });
    pdf.setAuthor(data.meta.company);
    pdf.setSubject(`Recruitment specification ${data.doc.ref}`);
    pdf.setKeywords(['recruitment', 'job requirement', data.job.department, data.job.employmentType].filter(Boolean));
    pdf.setCreator('Bitroxia PMS');
    pdf.setProducer('Bitroxia PMS (pdfkit + pdf-lib)');
    pdf.setLanguage('en-IN');
    pdf.setCreationDate(new Date());
    pdf.setModificationDate(new Date());

    process.stdout.write(Buffer.from(await pdf.save()));
}

main().catch((error) => {
    process.stderr.write(String(error?.stack || error));
    process.exit(1);
});
