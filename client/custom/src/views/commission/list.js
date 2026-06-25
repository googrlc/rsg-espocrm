define('custom:views/commission/list', ['exports', 'views/list'], function (_exports, _list) {
  "use strict";
  Object.defineProperty(_exports, "__esModule", { value: true });
  var Dep = _list.default || _list;

  _exports.default = Dep.extend({
    scope: 'Commission',

    setup: function () {
      this.scope = 'Commission';
      this.activeTab = 'unreconciled';
      this.searchQuery = '';
      this.sort = { key: 'expectedPaymentDate', dir: 'asc' };
      this.allRows = [];
      this.loading = true;
      this.error = false;

      this.select = 'id,name,accountName,accountId,policyName,carrier,lineOfBusiness,commissionType,' +
        'effectiveDate,expectedPaymentDate,estimatedCommission,postedAmount,varianceAmount,variancePercent,' +
        'status,reconciliationStatus,ledgerSyncStatus,producer,commissionRate,writtenPremium';

      this.columns = [
        { key: 'accountName', label: 'Account', type: 'link', sortable: true },
        { key: 'policyName', label: 'Policy', type: 'text', sortable: true },
        { key: 'commissionType', label: 'Type', type: 'badge', sortable: true },
        { key: 'carrier', label: 'Carrier', type: 'text', sortable: true },
        { key: 'effectiveDate', label: 'Effective', type: 'date', sortable: true },
        { key: 'expectedPaymentDate', label: 'Expected Pay', type: 'date', sortable: true },
        { key: 'estimatedCommission', label: 'Estimated', type: 'currency', sortable: true },
        { key: 'postedAmount', label: 'Posted', type: 'currency', sortable: true },
        { key: 'varianceAmount', label: 'Variance', type: 'currency', sortable: true },
        { key: 'status', label: 'Status', type: 'badge', sortable: true },
        { key: 'ledgerSyncStatus', label: 'Ledger', type: 'badge', sortable: true },
        { key: 'producer', label: 'Producer', type: 'text', sortable: true },
        { key: '_actions', label: 'Actions', type: 'actions', sortable: false }
      ];

      this.tabs = [
        { key: 'unreconciled', label: 'Unreconciled', recon: 'Unreconciled', color: '#b45309' },
        { key: 'reconciled', label: 'Reconciled', recon: 'Reconciled', color: '#16a34a' },
        { key: 'disputed', label: 'Disputed', recon: 'Disputed', color: '#dc2626' },
        { key: 'all', label: 'All', recon: null, color: '#2262e8' }
      ];
    },

    afterRender: function () {
      var self = this;
      this._injectCss();
      this._bindEvents();
      this._renderShell();
      this._fetch().then(function () {
        self.loading = false;
        self._renderTiles();
        self._renderTabs();
        self._renderTable();
      }).catch(function () {
        self.loading = false; self.error = true;
        self._renderError();
      });
    },

    _fetch: function () {
      // EspoCRM caps maxSize at 200 per request, so page through the full set.
      var self = this, out = [], offset = 0, pageSize = 200;
      var nextPage = function () {
        return Espo.Ajax.getRequest('Commission', { maxSize: pageSize, select: self.select, offset: offset })
          .then(function (data) {
            var list = (data && data.list) ? data.list : [];
            out = out.concat(list);
            if (list.length < pageSize) { self.allRows = out; return; }
            offset += pageSize;
            return nextPage();
          });
      };
      return nextPage();
    },

    _bindEvents: function () {
      var self = this;
      this.$el.on('click', '[data-tab]', function () {
        var tab = $(this).data('tab'); if (self._tabDef(tab) === null) return;
        self.activeTab = tab;
        self.$el.find('[data-tab]').removeClass('rsg-cw-tab-active');
        $(this).addClass('rsg-cw-tab-active');
        self._renderTable();
      });
      this.$el.on('input', '#rsg-cw-search', function () {
        var el = this; if (self.searchTimer) clearTimeout(self.searchTimer);
        self.searchTimer = setTimeout(function () { self.searchQuery = el.value.trim().toLowerCase(); self._renderTable(); }, 200);
      });
      this.$el.on('click', 'th[data-sort-key]', function () {
        var key = $(this).data('sort-key');
        self.sort = (self.sort.key === key) ? { key: key, dir: self.sort.dir === 'asc' ? 'desc' : 'asc' } : { key: key, dir: 'asc' };
        self._renderTable();
      });
      this.$el.on('click', '#rsg-cw-refresh', function () { self._refresh(); });
      this.$el.on('click', '.rsg-cw-row-open', function (e) {
        e.stopPropagation(); self.getRouter().navigate('#Commission/view/' + $(this).data('id'), { trigger: true });
      });
      this.$el.on('click', '.rsg-cw-quick', function (e) {
        e.stopPropagation(); self._quickAction(String($(this).data('id')), String($(this).data('recon')));
      });
    },

    _refresh: function () {
      var self = this; this.loading = true; this.error = false; this._renderShell();
      this._fetch().then(function () {
        self.loading = false; self._renderTiles(); self._renderTabs(); self._renderTable();
      }).catch(function () { self.loading = false; self.error = true; self._renderError(); });
    },

    _quickAction: function (id, recon) {
      var self = this;
      var payload = { reconciliationStatus: recon };
      var done = function () {
        var row = self.allRows.find(function (r) { return r.id === id; });
        if (row) { row.reconciliationStatus = recon; }
        self._renderTiles(); self._renderTabs(); self._renderTable();
      };
      var fail = function () { self._toast('Could not update commission ' + id.slice(-6), 'danger'); };
      // Espo.Ajax.* attaches the session auth header; a raw $.ajax would 401.
      if (Espo.Ajax && Espo.Ajax.patchRequest) {
        Espo.Ajax.patchRequest('Commission/' + id, payload).then(done).catch(fail);
      } else if (Espo.Ajax && Espo.Ajax.putRequest) {
        Espo.Ajax.putRequest('Commission/' + id, payload).then(done).catch(fail);
      } else {
        Espo.Ajax.postRequest('Commission/' + id, payload).then(done).catch(fail);
      }
    },

    _tabDef: function (key) {
      for (var i = 0; i < this.tabs.length; i++) if (this.tabs[i].key === key) return this.tabs[i];
      return null;
    },

    _rowsForTab: function () {
      var def = this._tabDef(this.activeTab);
      var rows = this.allRows;
      if (def && def.recon) rows = rows.filter(function (r) { return r.reconciliationStatus === def.recon; });
      var q = this.searchQuery;
      if (q) {
        rows = rows.filter(function (r) {
          return [r.accountName, r.carrier, r.policyName, r.producer, r.name].some(function (v) {
            return v && String(v).toLowerCase().indexOf(q) !== -1;
          });
        });
      }
      var s = this.sort;
      rows = rows.slice().sort(function (a, b) {
        var va = a[s.key], vb = b[s.key];
        if (va == null && vb == null) return 0;
        if (va == null) return 1;
        if (vb == null) return -1;
        if (typeof va === 'number' && typeof vb === 'number') return s.dir === 'asc' ? va - vb : vb - va;
        var cmp = String(va).localeCompare(String(vb), undefined, { numeric: true });
        return s.dir === 'asc' ? cmp : -cmp;
      });
      return rows;
    },

    _computeTiles: function () {
      var rows = this.allRows;
      var sum = function (arr, f) { return arr.reduce(function (t, r) { return t + (Number(r[f]) || 0); }, 0); };
      var unreconciled = rows.filter(function (r) { return r.reconciliationStatus === 'Unreconciled'; });
      var reconciled = rows.filter(function (r) { return r.reconciliationStatus === 'Reconciled'; });
      var disputed = rows.filter(function (r) { return r.reconciliationStatus === 'Disputed'; });
      var needsAttn = rows.filter(function (r) {
        return r.status === 'Overdue' || r.ledgerSyncStatus === 'Error' ||
          (Math.abs(Number(r.variancePercent) || 0) >= 15);
      });
      return {
        unreconciled: { count: unreconciled.length, total: sum(unreconciled, 'estimatedCommission') },
        reconciled: { count: reconciled.length, total: sum(reconciled, 'postedAmount') },
        disputed: { count: disputed.length, total: sum(disputed, 'varianceAmount') },
        needsAttn: { count: needsAttn.length }
      };
    },

    _renderShell: function () {
      this.$el.html(
        '<div class="rsg-cw">' +
        '<div class="rsg-cw-header"><div><h2 class="rsg-cw-title">Commission Workbench</h2>' +
        '<p class="rsg-cw-sub">Review, reconcile, and clear commission records</p></div>' +
        '<div class="rsg-cw-actions"><button id="rsg-cw-refresh" class="btn btn-default"><span class="fas fa-rotate-right"></span> Refresh</button></div></div>' +
        '<div id="rsg-cw-tiles"></div>' +
        '<div id="rsg-cw-tabs"></div>' +
        '<div class="rsg-cw-toolbar"><div class="rsg-cw-search-wrap"><span class="fas fa-magnifying-glass rsg-cw-search-icon"></span>' +
        '<input id="rsg-cw-search" type="text" placeholder="Search account, policy, carrier, producer" /></div></div>' +
        '<div id="rsg-cw-table"></div></div>'
      );
    },

    _renderTiles: function () {
      var t = this._computeTiles();
      var cur = Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 });
      var tile = function (label, count, money, cls) {
        return '<div class="rsg-cw-tile ' + cls + '"><div class="rsg-cw-tile-label">' + label + '</div>' +
          '<div class="rsg-cw-tile-count">' + count + '</div>' +
          (money != null ? '<div class="rsg-cw-tile-money">' + cur.format(money) + '</div>' : '') + '</div>';
      };
      this.$el.find('#rsg-cw-tiles').html(
        tile('Unreconciled', t.unreconciled.count, t.unreconciled.total, 'rsg-cw-tile-amber') +
        tile('Reconciled', t.reconciled.count, t.reconciled.total, 'rsg-cw-tile-green') +
        tile('Disputed', t.disputed.count, t.disputed.total, 'rsg-cw-tile-red') +
        tile('Needs Attention', t.needsAttn.count, null, t.needsAttn.count > 0 ? 'rsg-cw-tile-red' : 'rsg-cw-tile-amber')
      );
    },

    _renderTabs: function () {
      var t = this._computeTiles();
      var counts = { unreconciled: t.unreconciled.count, reconciled: t.reconciled.count, disputed: t.disputed.count, all: this.allRows.length };
      var html = '<div class="rsg-cw-tabs">';
      var self = this;
      this.tabs.forEach(function (tab) {
        var c = counts[tab.key] != null ? ' <span class="rsg-cw-tab-count">(' + counts[tab.key] + ')</span>' : '';
        html += '<button class="rsg-cw-tab' + (tab.key === self.activeTab ? ' rsg-cw-tab-active' : '') + '" data-tab="' + tab.key + '" style="--rsg-cw-accent:' + tab.color + '">' + tab.label + c + '</button>';
      });
      html += '</div>';
      this.$el.find('#rsg-cw-tabs').html(html);
    },

    _renderTable: function () {
      if (this.loading) {
        this.$el.find('#rsg-cw-table').html('<div class="rsg-cw-state"><span class="fas fa-spinner fa-spin"></span> Loading commissions…</div>');
        return;
      }
      var self = this;
      var rows = this._rowsForTab();
      var s = this.sort;
      var html = '<div class="rsg-cw-table-wrap"><table class="rsg-cw-table"><thead><tr>';
      this.columns.forEach(function (col) {
        var arrow = col.sortable ? (s.key === col.key ? (s.dir === 'asc' ? ' ↑' : ' ↓') : ' ⇅') : '';
        var attr = col.sortable ? ' data-sort-key="' + col.key + '"' : '';
        html += '<th' + attr + ' class="' + (s.key === col.key ? 'rsg-cw-sorted' : '') + '">' + col.label + '<span class="rsg-cw-arrow">' + arrow + '</span></th>';
      });
      html += '</tr></thead><tbody>';
      if (rows.length === 0) {
        html += '<tr><td colspan="' + this.columns.length + '"><div class="rsg-cw-state">No ' + this._tabDef(this.activeTab).label.toLowerCase() + ' commissions found.</div></td></tr>';
      } else {
        rows.forEach(function (r) { html += self._renderRow(r); });
      }
      html += '</tbody></table></div>';
      this.$el.find('#rsg-cw-table').html(html);
    },

    _renderRow: function (r) {
      var recon = r.reconciliationStatus || '';
      var rail = recon === 'Reconciled' ? 'rsg-cw-rail-green' : recon === 'Disputed' ? 'rsg-cw-rail-red' : 'rsg-cw-rail-amber';
      var overdue = r.status === 'Overdue';
      var html = '<tr class="rsg-cw-row ' + rail + '" data-id="' + this._esc(r.id) + '">';
      var self = this;
      this.columns.forEach(function (col) {
        if (col.key === '_actions') {
          html += '<td class="rsg-cw-td-actions">' +
            '<button class="rsg-cw-act rsg-cw-row-open" data-id="' + self._esc(r.id) + '" title="Open"><span class="fas fa-up-right-from-square"></span></button>' +
            '<button class="rsg-cw-act rsg-cw-quick" data-id="' + self._esc(r.id) + '" data-recon="Reconciled" title="Mark Reconciled"' + (recon === 'Reconciled' ? ' disabled' : '') + '><span class="fas fa-check"></span></button>' +
            '<button class="rsg-cw-act rsg-cw-quick" data-id="' + self._esc(r.id) + '" data-recon="Disputed" title="Mark Disputed"' + (recon === 'Disputed' ? ' disabled' : '') + '><span class="fas fa-triangle-exclamation"></span></button>' +
            '<button class="rsg-cw-act rsg-cw-quick" data-id="' + self._esc(r.id) + '" data-recon="Unreconciled" title="Mark Unreconciled"' + (recon === 'Unreconciled' ? ' disabled' : '') + '><span class="fas fa-rotate-left"></span></button>' +
            '</td>';
          return;
        }
        var v = r[col.key];
        var cls = 'rsg-cw-td';
        var cell = '—';
        if (col.type === 'link') {
          cell = '<a href="#Account/view/' + self._esc(r.accountId || '') + '" onclick="event.stopPropagation()">' + self._esc(v || '—') + '</a>';
        } else if (col.type === 'badge') {
          cell = self._badge(v, col.key, overdue);
        } else if (col.type === 'currency') {
          cell = (v != null && v !== '') ? self._money(v) : '—';
          cls += ' rsg-cw-num';
          if (col.key === 'varianceAmount' && Number(v) < 0) cls += ' rsg-cw-neg';
        } else if (col.type === 'date') {
          cell = v ? self._date(v) : '—';
          if (col.key === 'expectedPaymentDate' && overdue) cls += ' rsg-cw-overdue-date';
        } else {
          cell = self._esc(v || '—');
        }
        html += '<td class="' + cls + '">' + cell + '</td>';
      });
      html += '</tr>';
      return html;
    },

    _renderError: function () {
      this.$el.find('#rsg-cw-table').html('<div class="rsg-cw-state rsg-cw-err">Could not load commissions. <button id="rsg-cw-refresh" class="btn btn-default">Retry</button></div>');
    },

    _badge: function (v, key, overdue) {
      if (!v) return '<span class="rsg-cw-badge rsg-cw-badge-muted">—</span>';
      var styles = {
        status: { 'Estimated': 'info', 'Posted': 'success', 'Overdue': 'danger' },
        ledgerSyncStatus: { 'Synced': 'success', 'Pending': 'warning', 'Error': 'danger' },
        commissionType: { 'New Business': 'primary', 'Renewal': 'success', 'Endorsement': 'warning' }
      };
      var map = styles[key] || {};
      var cls = map[v] || 'muted';
      return '<span class="rsg-cw-badge rsg-cw-badge-' + cls + '">' + this._esc(v) + '</span>';
    },

    _money: function (v) { return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 2 }).format(Number(v)); },
    _date: function (v) { var d = new Date(v); return isNaN(d) ? v : d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: '2-digit' }); },
    _esc: function (s) { return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); },
    _toast: function (msg, kind) { var t = $('<div class="rsg-cw-toast rsg-cw-toast-' + (kind || 'info') + '">' + this._esc(msg) + '</div>').appendTo('body'); setTimeout(function () { t.fadeOut(300, function () { $(this).remove(); }); }, 3200); },

    _injectCss: function () {
      if (document.getElementById('rsg-cw-css')) return;
      var css =
'.rsg-cw{padding:6px 2px 24px}.rsg-cw-header{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:14px;flex-wrap:wrap}.rsg-cw-title{font-size:1.35rem;font-weight:700;margin:0;color:#2262e8}.rsg-cw-sub{font-size:.84rem;color:#6b7280;margin:2px 0 0}.rsg-cw-actions{display:flex;gap:8px}' +
'.rsg-cw-tiles{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:14px}@media(max-width:760px){.rsg-cw-tiles{grid-template-columns:repeat(2,1fr)}}.rsg-cw-tile{border-radius:10px;padding:12px 14px;background:#fff;border:1px solid #e5ebf5;box-shadow:0 1px 3px rgba(14,31,67,.05)}.rsg-cw-tile-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;font-weight:700}.rsg-cw-tile-count{font-size:1.6rem;font-weight:800;margin-top:2px}.rsg-cw-tile-money{font-size:.9rem;font-weight:700;margin-top:1px}.rsg-cw-tile-amber{border-left:4px solid #f59e0b}.rsg-cw-tile-amber .rsg-cw-tile-money{color:#b45309}.rsg-cw-tile-green{border-left:4px solid #16a34a}.rsg-cw-tile-green .rsg-cw-tile-money{color:#15803d}.rsg-cw-tile-red{border-left:4px solid #dc2626}.rsg-cw-tile-red .rsg-cw-tile-money{color:#b91c1c}' +
'.rsg-cw-tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px}.rsg-cw-tab{border:1px solid #d7deee;background:#fff;border-radius:999px;padding:6px 14px;font-size:.82rem;font-weight:700;color:#3b4a66;cursor:pointer}.rsg-cw-tab:hover{background:#eef4ff}.rsg-cw-tab-active{background:var(--rsg-cw-accent,#2262e8);border-color:var(--rsg-cw-accent,#2262e8);color:#fff}.rsg-cw-tab-count{font-weight:600;opacity:.8}' +
'.rsg-cw-toolbar{margin-bottom:10px}.rsg-cw-search-wrap{position:relative;max-width:420px}.rsg-cw-search-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#9aa6bb}.rsg-cw-search-wrap input{width:100%;padding:8px 12px 8px 34px;border:1px solid #d7deee;border-radius:999px;font-size:.85rem;box-sizing:border-box}' +
'.rsg-cw-table-wrap{overflow-x:auto;border:1px solid #e5ebf5;border-radius:10px;background:#fff}.rsg-cw-table{width:100%;border-collapse:collapse;font-size:.82rem}.rsg-cw-table thead th{position:sticky;top:0;background:#f8fafc;text-align:left;padding:9px 10px;white-space:nowrap;cursor:pointer;color:#475069;font-weight:700;border-bottom:1px solid #e2e8f0}.rsg-cw-table thead th.rsg-cw-sorted{color:#2262e8}.rsg-cw-arrow{font-size:.7rem;color:#9aa6bb;margin-left:2px}.rsg-cw-table tbody td{padding:8px 10px;border-bottom:1px solid #f1f4fa;white-space:nowrap}.rsg-cw-row:hover>td{background:#f6f9ff}.rsg-cw-rail-green>td:first-child{box-shadow:inset 3px 0 0 #16a34a}.rsg-cw-rail-red>td:first-child{box-shadow:inset 3px 0 0 #dc2626}.rsg-cw-rail-amber>td:first-child{box-shadow:inset 3px 0 0 #f59e0b}.rsg-cw-num{text-align:right;font-variant-numeric:tabular-nums}.rsg-cw-neg{color:#dc2626}.rsg-cw-overdue-date{color:#dc2626;font-weight:700}' +
'.rsg-cw-badge{display:inline-block;padding:2px 9px;border-radius:999px;font-size:.72rem;font-weight:700}.rsg-cw-badge-success{background:#dcfce7;color:#15803d}.rsg-cw-badge-info{background:#dbeafe;color:#1d4ed8}.rsg-cw-badge-warning{background:#fef3c7;color:#b45309}.rsg-cw-badge-danger{background:#fee2e2;color:#b91c1c}.rsg-cw-badge-primary{background:#e0e7ff;color:#4338ca}.rsg-cw-badge-muted{background:#f1f5f9;color:#64748b}' +
'.rsg-cw-td-actions{white-space:nowrap}.rsg-cw-act{background:#fff;border:1px solid #d7deee;border-radius:7px;padding:4px 7px;cursor:pointer;color:#475069;font-size:.78rem;margin-right:3px}.rsg-cw-act:hover:not(:disabled){background:#eef4ff;color:#2262e8}.rsg-cw-act:disabled{opacity:.35;cursor:not-allowed}.rsg-cw-state{padding:28px;text-align:center;color:#8a97ad}.rsg-cw-err{color:#b91c1c}.rsg-cw-toast{position:fixed;bottom:18px;right:18px;z-index:9999;padding:10px 16px;border-radius:8px;background:#1d4ed8;color:#fff;font-weight:600;box-shadow:0 4px 12px rgba(0,0,0,.2)}.rsg-cw-toast-danger{background:#b91c1c}';
      var s = document.createElement('style'); s.id = 'rsg-cw-css'; s.textContent = css; document.head.appendChild(s);
    }
  });
});
