ICON_CHECK = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>'
ICON_ARROW = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>'

def module_icon_svg(path_d, extra=""):
    return f'<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">{path_d}{extra}</svg>'

ICONS = {
    "tasks": module_icon_svg('<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>'),
    "gantt": module_icon_svg('<path d="M3 4v16h18"/><rect x="6" y="14" width="6" height="3" rx="1"/><rect x="10" y="9" width="9" height="3" rx="1"/><rect x="6" y="19" width="12" height="0"/>'),
    "kanban": module_icon_svg('<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>'),
    "clock": module_icon_svg('<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>'),
    "calendar": module_icon_svg('<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18M8 2v4M16 2v4"/>'),
    "chart": module_icon_svg('<path d="M3 3v18h18"/><path d="M7 15l3-4 3 3 5-7"/>'),
    "ticket": module_icon_svg('<path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>'),
    "shield": module_icon_svg('<path d="M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6z"/>'),
    "rocket": module_icon_svg('<path d="M13 2L3 14h7l-1 8 10-12h-7z"/>'),
    "users": module_icon_svg('<circle cx="9" cy="7" r="3"/><path d="M2 20c0-3.9 3.1-7 7-7s7 3.1 7 7"/><path d="M16 3.1a4 4 0 010 7.8M22 20c0-2.8-2-5.2-4.7-6.4"/>'),
    "globe": module_icon_svg('<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 010 18M12 3a15 15 0 000 18"/>'),
    "code": module_icon_svg('<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>'),
    "star": module_icon_svg('<path d="M12 2l3 7h7l-5.5 4.5L18 21l-6-4-6 4 1.5-7.5L2 9h7z"/>'),
    "layers": module_icon_svg('<path d="M12 2l9 5-9 5-9-5 9-5z"/><path d="M3 12l9 5 9-5M3 16.5l9 5 9-5"/>'),
    "book": module_icon_svg('<path d="M4 4.5A2.5 2.5 0 016.5 2H20v16.5A2.5 2.5 0 0117.5 21H4z"/><path d="M4 4.5A2.5 2.5 0 006.5 22H20"/>'),
    "life-ring": module_icon_svg('<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3.5"/><path d="M5 5l4.5 4.5M19 5l-4.5 4.5M5 19l4.5-4.5M19 19l-4.5-4.5"/>'),
    "plug": module_icon_svg('<path d="M9 2v4M15 2v4M6 9h12l-1 5a5 5 0 01-10 0z"/><path d="M12 16v6"/>'),
    "briefcase": module_icon_svg('<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>'),
}

def illus_gantt(rows):
    """rows: list of (label, pct_start, pct_width)"""
    html = '<div class="illus-frame"><div class="illus-dots"><span></span><span></span><span></span></div><div class="illus-gantt">'
    for label, start, width in rows:
        html += f'<div class="gantt-row"><span class="label">{label}</span><div class="track"><i style="left:{start}%;width:{width}%"></i></div></div>'
    html += '</div></div>'
    return html

def illus_kanban(cols):
    """cols: list of (title, [card_texts])"""
    html = '<div class="illus-frame"><div class="illus-dots"><span></span><span></span><span></span></div><div class="illus-kanban">'
    for title, cards in cols:
        html += f'<div class="kcol"><span class="klabel">{title}</span>'
        for c in cards:
            html += f'<div class="kcard">{c}</div>'
        html += '</div>'
    html += '</div></div>'
    return html

def illus_calendar(pattern):
    """pattern: list of 21 class strings '', 'on', 'off'"""
    html = '<div class="illus-frame"><div class="illus-dots"><span></span><span></span><span></span></div><div class="illus-calendar">'
    for cls in pattern:
        html += f'<span class="{cls}"></span>'
    html += '</div></div>'
    return html

def illus_list(rows):
    """rows: list of (color, bold_text, sub_text, tag)"""
    html = '<div class="illus-frame"><div class="illus-dots"><span></span><span></span><span></span></div><div class="illus-list">'
    for color, bold, sub, tag in rows:
        html += f'<div class="lrow"><span class="ldot" style="background:{color}"></span><span class="ltext"><b>{bold}</b>{sub}</span><span class="ltag">{tag}</span></div>'
    html += '</div></div>'
    return html

def illus_chart(heights):
    html = '<div class="illus-frame"><div class="illus-dots"><span></span><span></span><span></span></div><div class="illus-chart">'
    for h in heights:
        html += f'<i style="height:{h}%"></i>'
    html += '</div></div>'
    return html
