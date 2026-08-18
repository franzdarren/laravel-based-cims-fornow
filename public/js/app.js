window.CIMS = (function () {
    'use strict';

    function $(sel, root) { return (root || document).querySelector(sel); }
    function $all(sel, root) { return [...(root || document).querySelectorAll(sel)]; }

    // ---------------------------------------------------------------
    // Toast
    // ---------------------------------------------------------------
    let toastTimer;
    function toast(message) {
        const el = $('#toast');
        if (!el) return;
        el.textContent = message;
        el.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => el.classList.remove('show'), 2600);
    }

    // ---------------------------------------------------------------
    // Modal — body content comes from a <template> already rendered by
    // Blade (so every field, action URL, and CSRF token is server-real;
    // JS only moves it into view). Trigger with:
    //   <button data-modal-open="my-template-id" data-modal-title="Edit thing">Edit</button>
    // ---------------------------------------------------------------
    const modal = {
        open(title, bodyHtml) {
            const back = $('#modalBack'), titleEl = $('#modalTitle'), bodyEl = $('#modalBody');
            if (!back || !bodyEl) return;
            titleEl.textContent = title || '';
            bodyEl.innerHTML = bodyHtml;
            back.classList.add('open');
            tables.init(bodyEl);
            combobox.init(bodyEl);
            permissions.init(bodyEl);
            document.dispatchEvent(new CustomEvent('cims:modal-opened', { detail: { root: bodyEl } }));
            const focusable = bodyEl.querySelector('input,select,textarea');
            if (focusable) focusable.focus();
        },
        close() {
            const back = $('#modalBack'), bodyEl = $('#modalBody');
            if (!back) return;
            back.classList.remove('open');
            if (bodyEl) bodyEl.innerHTML = '';
        }
    };

    // ---------------------------------------------------------------
    // Tables — search filter, sortable columns, client-side pagination.
    // Opt in with <table id="..." data-enhance>. A results-count-free
    // "no records" row should carry class="empty-row" so it's excluded.
    // Search box: <input data-table-search="tableId">
    // Sortable header cell: <th data-sort="text|number|date">
    // A cell can override the sort/filter value via data-sort-value.
    // ---------------------------------------------------------------
    const PAGE_SIZE = 10;
    const tables = {
        init(root) {
            $all('table[data-enhance]', root || document).forEach(table => {
                if (table._cimsInit) return;
                table._cimsInit = true;
                table._page = 1;
                this.bindSearch(table);
                this.bindSort(table);
                this.buildPaginationHolder(table);
                this.refresh(table);
            });
        },
        bindSearch(table) {
            if (!table.id) return;
            $all('input[data-table-search="' + table.id + '"]').forEach(input => {
                input.addEventListener('input', () => {
                    const q = input.value.toLowerCase().trim();
                    $all('tbody tr', table).forEach(row => {
                        if (row.classList.contains('empty-row')) return;
                        row.dataset.searchHidden = (!q || row.textContent.toLowerCase().includes(q)) ? '0' : '1';
                    });
                    table._page = 1;
                    this.refresh(table);
                });
            });
        },
        bindSort(table) {
            const headRow = table.tHead && table.tHead.rows[0];
            if (!headRow) return;
            [...headRow.cells].forEach((th, index) => {
                if (!th.dataset.sort) return;
                th.classList.add('sortable-col');
                th.addEventListener('click', () => {
                    const dir = th.dataset.sortDir === 'asc' ? 'desc' : 'asc';
                    [...headRow.cells].forEach(h => { h.dataset.sortDir = ''; h.classList.remove('sort-asc', 'sort-desc'); });
                    th.dataset.sortDir = dir;
                    th.classList.add(dir === 'asc' ? 'sort-asc' : 'sort-desc');
                    const type = th.dataset.sort;
                    const rows = $all('tbody tr', table).filter(r => !r.classList.contains('empty-row'));
                    rows.sort((a, b) => {
                        const av = (a.cells[index] && (a.cells[index].dataset.sortValue ?? a.cells[index].textContent) || '').trim();
                        const bv = (b.cells[index] && (b.cells[index].dataset.sortValue ?? b.cells[index].textContent) || '').trim();
                        let cmp;
                        if (type === 'number') cmp = (parseFloat(av) || 0) - (parseFloat(bv) || 0);
                        else if (type === 'date') cmp = (Date.parse(av) || 0) - (Date.parse(bv) || 0);
                        else cmp = av.localeCompare(bv, undefined, { numeric: true, sensitivity: 'base' });
                        return dir === 'asc' ? cmp : -cmp;
                    });
                    rows.forEach(r => table.tBodies[0].appendChild(r));
                    table._page = 1;
                    this.refresh(table);
                });
            });
        },
        buildPaginationHolder(table) {
            const wrap = table.closest('.table-wrap') || table.parentElement;
            const holder = document.createElement('div');
            holder.className = 'table-pagination';
            wrap.insertAdjacentElement('afterend', holder);
            table._paginationHolder = holder;
        },
        refresh(table) {
            const rows = $all('tbody tr', table).filter(r => !r.classList.contains('empty-row'));
            const eligible = rows.filter(r => r.dataset.searchHidden !== '1' && r.dataset.filterHidden !== '1');
            const pages = Math.max(1, Math.ceil(eligible.length / PAGE_SIZE));
            table._page = Math.min(Math.max(1, table._page || 1), pages);
            const start = (table._page - 1) * PAGE_SIZE, end = start + PAGE_SIZE;
            rows.forEach(r => { r.style.display = 'none'; });
            eligible.slice(start, end).forEach(r => { r.style.display = ''; });
            const emptyRow = table.querySelector('tbody tr.empty-row');
            if (emptyRow) emptyRow.style.display = rows.length ? 'none' : '';
            const holder = table._paginationHolder;
            if (!holder) return;
            if (eligible.length <= PAGE_SIZE) { holder.innerHTML = ''; return; }
            const first = start + 1, last = Math.min(end, eligible.length);
            let html = '<span class="page-info">Showing ' + first + '–' + last + ' of ' + eligible.length + ' records</span><div class="page-controls">';
            html += '<button type="button" class="btn small" data-page="prev"' + (table._page <= 1 ? ' disabled' : '') + '>Previous</button>';
            const wStart = Math.max(1, table._page - 2), wEnd = Math.min(pages, wStart + 4);
            for (let p = wStart; p <= wEnd; p++) {
                html += '<button type="button" class="btn small' + (p === table._page ? ' current' : '') + '" data-page="' + p + '">' + p + '</button>';
            }
            html += '<button type="button" class="btn small" data-page="next"' + (table._page >= pages ? ' disabled' : '') + '>Next</button></div>';
            holder.innerHTML = html;
            $all('button[data-page]', holder).forEach(btn => {
                btn.onclick = () => {
                    const p = btn.dataset.page;
                    table._page = p === 'prev' ? table._page - 1 : p === 'next' ? table._page + 1 : Number(p);
                    this.refresh(table);
                };
            });
        }
    };

    // ---------------------------------------------------------------
    // Combobox — progressively enhances a normal <select> (with
    // <optgroup> labels) into a type-to-filter dropdown. Markup:
    // <div data-combobox>
    //   <input type="text" placeholder="Search or select…">
    //   <select name="item_id">...grouped <option>s...</select>
    //   <div class="item-combo-menu"></div>
    // </div>
    // ---------------------------------------------------------------
    const combobox = {
        init(root) {
            $all('[data-combobox]', root || document).forEach(wrap => {
                if (wrap._cimsInit) return;
                wrap._cimsInit = true;
                const select = wrap.querySelector('select');
                const input = wrap.querySelector('input[type="text"]');
                const menu = wrap.querySelector('.item-combo-menu');
                if (!select || !input || !menu) return;
                select.hidden = true;

                const options = $all('option', select)
                    .filter(o => o.value !== '')
                    .map(o => ({ value: o.value, label: o.textContent.trim(), group: (o.closest('optgroup') || {}).label || '' }));

                const render = query => {
                    const q = query.trim().toLowerCase();
                    const groups = {};
                    options.forEach(o => {
                        if (q && !o.label.toLowerCase().includes(q)) return;
                        (groups[o.group] = groups[o.group] || []).push(o);
                    });
                    const keys = Object.keys(groups);
                    if (!keys.length) { menu.innerHTML = '<div class="item-combo-empty">No matching items</div>'; return; }
                    menu.innerHTML = keys.map(g =>
                        (g ? '<div class="item-combo-group">' + g + '</div>' : '') +
                        groups[g].map(o => '<button type="button" class="item-combo-option" data-value="' + o.value + '">' + o.label + '</button>').join('')
                    ).join('');
                    $all('.item-combo-option', menu).forEach(btn => {
                        btn.onmousedown = e => e.preventDefault();
                        btn.onclick = () => {
                            select.value = btn.dataset.value;
                            input.value = btn.textContent.trim();
                            menu.classList.remove('open');
                            select.dispatchEvent(new Event('change', { bubbles: true }));
                        };
                    });
                };

                const preselected = options.find(o => o.value === select.value);
                input.value = preselected ? preselected.label : '';
                input.addEventListener('focus', () => { render(''); menu.classList.add('open'); input.select(); });
                input.addEventListener('click', () => { if (!menu.classList.contains('open')) { render(input.value); menu.classList.add('open'); } });
                input.addEventListener('input', () => { select.value = ''; render(input.value); menu.classList.add('open'); });
                input.addEventListener('blur', () => setTimeout(() => menu.classList.remove('open'), 120));
                input.addEventListener('keydown', e => { if (e.key === 'Escape') { menu.classList.remove('open'); input.blur(); } });
            });
        }
    };

    // ---------------------------------------------------------------
    // Permission picker (Roles form) — a checkbox group with a live
    // "N selected" counter plus Select All / Clear buttons. Markup:
    // <div class="permission-panel" data-permission-panel>
    //   <strong data-permission-count></strong>
    //   <button type="button" data-permission-select-all>Select all</button>
    //   <button type="button" data-permission-clear>Clear</button>
    //   ...<input type="checkbox" class="role-permission">...
    // </div>
    // ---------------------------------------------------------------
    const permissions = {
        init(root) {
            $all('[data-permission-panel]', root || document).forEach(panel => {
                if (panel._cimsInit) return;
                panel._cimsInit = true;
                const boxes = $all('.role-permission', panel);
                const counter = $('[data-permission-count]', panel);
                const sync = () => { if (counter) counter.textContent = boxes.filter(b => b.checked).length + ' selected'; };
                boxes.forEach(b => b.addEventListener('change', sync));
                const selectAll = $('[data-permission-select-all]', panel);
                if (selectAll) selectAll.addEventListener('click', () => { boxes.forEach(b => b.checked = true); sync(); });
                const clear = $('[data-permission-clear]', panel);
                if (clear) clear.addEventListener('click', () => { boxes.forEach(b => b.checked = false); sync(); });
                sync();
            });
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        tables.init(document);
        combobox.init(document);
        permissions.init(document);

        const modalBack = $('#modalBack');
        if (modalBack) modalBack.addEventListener('click', e => { if (e.target === modalBack) modal.close(); });
        const modalCloseBtn = $('#modalClose');
        if (modalCloseBtn) modalCloseBtn.addEventListener('click', () => modal.close());
        document.addEventListener('keydown', e => { if (e.key === 'Escape') modal.close(); });

        document.addEventListener('click', e => {
            const trigger = e.target.closest('[data-modal-open]');
            if (!trigger) return;
            const source = document.getElementById(trigger.dataset.modalOpen);
            if (!source) return;
            modal.open(trigger.dataset.modalTitle || '', source.innerHTML);
        });

        if (window.CIMS_STATUS) toast(window.CIMS_STATUS);
    });

    return { toast, modal, tables, combobox, permissions, $, $all };
})();
