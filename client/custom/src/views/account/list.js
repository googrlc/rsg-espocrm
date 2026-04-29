define('custom:views/account/list', ['exports', 'views/list'], function (_exports, _list) {
  "use strict";
  Object.defineProperty(_exports, "__esModule", { value: true });
  var Dep = _list.default || _list;
  (function () {
  if (!document.getElementById('rsg-select-nav-css')) {
    var s = document.createElement('style'); s.id = 'rsg-select-nav-css';
    s.textContent = '.rsg-th-cb,.rsg-td-cb{width:36px!important;min-width:36px!important;padding:0 8px!important;text-align:center!important;vertical-align:middle!important}.rsg-th-cb input[type=checkbox],.rsg-td-cb input[type=checkbox],.rsg-row-cb{width:16px;height:16px;cursor:pointer;accent-color:#2262e8}.rsg-row-selected>td{background:#eef4ff!important}.rsg-row-selected:hover>td{background:#dce8ff!important}.rsg-selection-bar{display:flex;align-items:center;gap:12px;background:#e8f0ff;border:1px solid #b8ceff;border-radius:10px;padding:8px 14px;margin-bottom:8px;font-size:.85rem;font-weight:600;color:#1d4eb4}#rsg-selected-count{flex:1}.rsg-sel-btn{background:transparent;border:1px solid #8aafff;border-radius:999px;color:#1d4eb4;font-size:.78rem;font-weight:700;padding:4px 12px;cursor:pointer}.rsg-sel-btn:hover{background:#d0e0ff}.rsg-record-nav{display:flex;align-items:center;justify-content:space-between;padding:8px 16px;margin-bottom:12px;background:rgba(255,255,255,.92);border:1px solid rgba(188,202,232,.7);border-radius:12px;box-shadow:0 2px 8px rgba(14,31,67,.06)}.rsg-nav-back{font-size:.82rem;font-weight:700;color:#2262e8;text-decoration:none}.rsg-nav-back:hover{text-decoration:underline}.rsg-nav-center{display:flex;align-items:center;gap:10px}.rsg-nav-pos{font-size:.8rem;color:#61708f;font-weight:600;min-width:60px;text-align:center}.rsg-nav-btn{background:#fff;border:1px solid #d7deee;border-radius:999px;color:#234170;font-size:.8rem;font-weight:700;padding:5px 14px;cursor:pointer;transition:background .15s,border-color .15s}.rsg-nav-btn:hover:not(:disabled){background:#eef4ff;border-color:#2262e8;color:#2262e8}.rsg-nav-btn.rsg-nav-disabled,.rsg-nav-btn:disabled{opacity:.35;cursor:not-allowed}';
    document.head.appendChild(s);
  }
  if (!window._rsgNavInit) {
    window._rsgNavInit = true;
    function _rsgInjectNav() {
      var h = window.location.hash, m = h.match(/^#Account\/view\/([^\/\?#]+)/);
      if (!m) return;
      if (document.getElementById('rsg-record-nav')) return;
      var lj; try { lj = sessionStorage.getItem('rsg-nav-list'); } catch(e) {}
      if (!lj) return;
      var ids; try { ids = JSON.parse(lj); } catch(e) { return; }
      if (!ids || !ids.length) return;
      var idx = ids.indexOf(m[1]);
      if (idx === -1) return;
      var $a = document.querySelector('.detail-button-container');
      if (!$a) return;
      var hp = idx > 0, hn = idx < ids.length - 1;
      var pid = hp ? ids[idx-1] : null, nid = hn ? ids[idx+1] : null;
      var nav = document.createElement('div');
      nav.id = 'rsg-record-nav'; nav.className = 'rsg-record-nav';
      nav.innerHTML = '<a href="#Account" class="rsg-nav-back">&#8592; All Accounts</a>' +
        '<div class="rsg-nav-center">' +
        '<button class="rsg-nav-btn' + (hp ? '' : ' rsg-nav-disabled') + '" id="rsg-nav-prev"' + (hp ? '' : ' disabled') + '>&#8592; Prev</button>' +
        '<span class="rsg-nav-pos">' + (idx+1) + ' of ' + ids.length + '</span>' +
        '<button class="rsg-nav-btn' + (hn ? '' : ' rsg-nav-disabled') + '" id="rsg-nav-next"' + (hn ? '' : ' disabled') + '>Next &#8594;</button></div>';
      $a.parentNode.insertBefore(nav, $a);
      if (pid) { document.getElementById('rsg-nav-prev').onclick = function() { window.location.hash = '#Account/view/' + pid; }; }
      if (nid) { document.getElementById('rsg-nav-next').onclick = function() { window.location.hash = '#Account/view/' + nid; }; }
    }
    new MutationObserver(function() {
      if (/^#Account\/view\//.test(window.location.hash) && !document.getElementById('rsg-record-nav')) _rsgInjectNav();
    }).observe(document.body, { childList: true, subtree: true });
    window.addEventListener('hashchange', function() {
      var o = document.getElementById('rsg-record-nav'); if (o) o.parentNode.removeChild(o);
      setTimeout(_rsgInjectNav, 300);
    });
  }
}())
  _exports.default = Dep.extend({
    template: 'custom:account/list',
    setup: function () {
      this.scope = 'Account'; this.activeTab = 'commercial'; this.searchQuery = {}; this.sortState = {}; this.cachedData = {}; this.selectedIds = {};
      this.primaryTypes = ['Commercial Lines', 'Personal Lines', 'Prospect'];
      this.tabDefs = {
        commercial: { label: 'Commercial Lines', color: '#2563eb',
          where: [{ type: 'equals', attribute: 'accountType', value: 'Commercial Lines' },{ type: 'in', attribute: 'accountStatus', value: ['Active','Urgent','Renewing','At Risk'] }],
          select: 'id,name,industry,phoneNumber,accountStatus,assignedUserName',
          columns: [{ key: 'name', label: 'Account', type: 'link', sortable: true },{ key: 'industry', label: 'Industry', type: 'text', sortable: true },{ key: '_totalPremium', label: 'Premium', type: 'currency', sortable: true },{ key: 'phoneNumber', label: 'Phone', type: 'text', sortable: true },{ key: 'accountStatus', label: 'Status', type: 'badge', sortable: true },{ key: 'assignedUserName', label: 'Assigned', type: 'text', sortable: true }],
          defaultSort: { key: '_totalPremium', dir: 'desc' } },
        personal: { label: 'Personal Lines', color: '#16a34a',
          where: [{ type: 'equals', attribute: 'accountType', value: 'Personal Lines' },{ type: 'in', attribute: 'accountStatus', value: ['Active','Urgent','Renewing','At Risk'] }],
          select: 'id,name,phoneNumber,accountStatus,csrName',
          columns: [{ key: 'name', label: 'Account', type: 'link', sortable: true },{ key: 'phoneNumber', label: 'Phone', type: 'text', sortable: true },{ key: '_totalPremium', label: 'Premium', type: 'currency', sortable: true },{ key: 'accountStatus', label: 'Status', type: 'badge', sortable: true },{ key: 'csrName', label: 'CSR', type: 'text', sortable: true }],
          defaultSort: { key: '_totalPremium', dir: 'desc' } },
        prospect: { label: 'Prospects', color: '#b45309',
          where: [{ type: 'equals', attribute: 'accountType', value: 'Prospect' }],
          select: 'id,name,industry,estimatedPremium,stage,assignedUserName',
          columns: [{ key: 'name', label: 'Account', type: 'link', sortable: true },{ key: 'industry', label: 'Industry', type: 'text', sortable: true },{ key: 'estimatedPremium', label: 'Est. Premium', type: 'currency', sortable: true },{ key: 'stage', label: 'Stage', type: 'badge', sortable: true },{ key: 'assignedUserName', label: 'Assigned', type: 'text', sortable: true }],
          defaultSort: { key: 'estimatedPremium', dir: 'desc' } },
        inactive: { label: 'Inactive', color: '#6b7280',
          where: [{ type: 'equals', attribute: 'accountStatus', value: 'Inactive' }],
          select: 'id,name,accountType,assignedUserName',
          columns: [{ key: 'name', label: 'Account', type: 'link', sortable: true },{ key: 'accountType', label: 'Type', type: 'text', sortable: true },{ key: '_totalPremium', label: 'Last Premium', type: 'currency', sortable: true },{ key: 'assignedUserName', label: 'Assigned', type: 'text', sortable: true }],
          defaultSort: { key: '_totalPremium', dir: 'desc' } },
        needsReview: { label: 'Needs Review', color: '#f59e0b',
          where: [{ type: 'notEquals', attribute: 'accountStatus', value: 'Inactive' }],
          clientFilter: true,
          select: 'id,name,accountType,accountStatus,assignedUserName',
          columns: [{ key: 'name', label: 'Account', type: 'link', sortable: true },{ key: 'accountType', label: 'Type', type: 'text', sortable: true },{ key: '_totalPremium', label: 'Premium', type: 'currency', sortable: true },{ key: 'accountStatus', label: 'Status', type: 'badge', sortable: true },{ key: 'assignedUserName', label: 'Assigned', type: 'text', sortable: true }],
          defaultSort: { key: 'name', dir: 'asc' } }
      };
      this.counts = {};
    },
    afterRender: function () { this._bindEvents(); this._loadTab(this.activeTab, true); },
    _bindEvents: function () {
      var self = this;
      this.$el.on('click', '[data-tab]', function () { var tab = $(this).data('tab'); if (tab !== self.activeTab) { self.activeTab = tab; self.$el.find('[data-tab]').removeClass('rsg-tab-active'); $(this).addClass('rsg-tab-active'); self._loadTab(tab, true); } });
      this.$el.on('input', '#rsg-search', function () { self.searchQuery[self.activeTab] = $(this).val(); self._renderTable(self.activeTab); });
      this.$el.on('click', 'th[data-sort-key]', function () { var key = $(this).data('sort-key'); var cur = self.sortState[self.activeTab] || Object.assign({}, self.tabDefs[self.activeTab].defaultSort); self.sortState[self.activeTab] = cur.key === key ? { key: key, dir: cur.dir === 'asc' ? 'desc' : 'asc' } : { key: key, dir: 'asc' }; self._renderTable(self.activeTab); });
      this.$el.on('click', '#rsg-new-btn', function () { self.getRouter().navigate('#Account/create', { trigger: true }); });
      this.$el.on('change', '#rsg-select-all', function () { var checked = $(this).is(':checked'); if (!self.selectedIds[self.activeTab]) self.selectedIds[self.activeTab] = {}; self.$el.find('.rsg-row-cb').each(function () { var id = $(this).data('id'); $(this).prop('checked', checked); if (checked) { self.selectedIds[self.activeTab][id] = true; } else { delete self.selectedIds[self.activeTab][id]; } }); self.$el.find('.rsg-row').toggleClass('rsg-row-selected', checked); self._updateSelectionBar(); });
      this.$el.on('change', '.rsg-row-cb', function (e) { e.stopPropagation(); var id = $(this).data('id'); if (!self.selectedIds[self.activeTab]) self.selectedIds[self.activeTab] = {}; if ($(this).is(':checked')) { self.selectedIds[self.activeTab][id] = true; $(this).closest('tr').addClass('rsg-row-selected'); } else { delete self.selectedIds[self.activeTab][id]; $(this).closest('tr').removeClass('rsg-row-selected'); } var total = self.$el.find('.rsg-row-cb').length, checked = self.$el.find('.rsg-row-cb:checked').length; self.$el.find('#rsg-select-all').prop('indeterminate', checked > 0 && checked < total).prop('checked', checked === total && total > 0); self._updateSelectionBar(); });
      this.$el.on('click', '.rsg-td-cb', function (e) { e.stopPropagation(); });
      this.$el.on('click', '#rsg-clear-selection', function () { self.selectedIds[self.activeTab] = {}; self.$el.find('.rsg-row-cb').prop('checked', false); self.$el.find('.rsg-row').removeClass('rsg-row-selected'); self.$el.find('#rsg-select-all').prop('checked', false).prop('indeterminate', false); self._updateSelectionBar(); });
    },
    _updateSelectionBar: function () { var selected = this.selectedIds[this.activeTab] || {}, count = Object.keys(selected).length, $bar = this.$el.find('#rsg-selection-bar'); if (count > 0) { $bar.find('#rsg-selected-count').text(count + ' record' + (count !== 1 ? 's' : '') + ' selected'); $bar.show(); } else { $bar.hide(); } },
    _loadTab: function (tabId, fetchFresh) {
      var self = this, def = this.tabDefs[tabId]; this._showTableLoading(tabId);
      if (!fetchFresh && this.cachedData[tabId]) { this._renderTable(tabId); return; }
      Espo.Ajax.getRequest('Account', { maxSize: 200, offset: 0, select: def.select, where: def.where }).then(function (data) {
        var accounts = data.list || [];
        if (def.clientFilter) accounts = accounts.filter(function (a) { return !a.accountType || self.primaryTypes.indexOf(a.accountType) === -1; });
        self.counts[tabId] = accounts.length; self._updateCount(tabId);
        if (accounts.length === 0) { self.cachedData[tabId] = accounts; self._renderTable(tabId); return; }
        var accountIds = accounts.map(function (a) { return a.id; });
        Espo.Ajax.getRequest('Policy', { maxSize: 200, offset: 0, select: 'id,accountId,premium_amount', where: [{ type: 'in', attribute: 'accountId', value: accountIds },{ type: 'in', attribute: 'status', value: ['Active', 'Renewing'] }] }).then(function (policyData) {
          var premiumMap = {}; (policyData.list || []).forEach(function (p) { if (p.accountId && p.premium_amount) premiumMap[p.accountId] = (premiumMap[p.accountId] || 0) + p.premium_amount; });
          accounts.forEach(function (a) { a._totalPremium = premiumMap[a.id] || 0; });
          self.cachedData[tabId] = accounts; self._renderTable(tabId);
        }).catch(function () { self.cachedData[tabId] = accounts; self._renderTable(tabId); });
      }).catch(function (xhr) { self._showTableError(tabId, 'API error ' + (xhr && xhr.status ? xhr.status : 'unknown')); });
    },
    _updateCount: function (tabId) { this.$el.find('[data-tab-count="' + tabId + '"]').text(this.counts[tabId] || ''); },
    _showTableLoading: function (tabId) { this.$el.find('#rsg-table-container').html('<div class="rsg-state-msg"><span class="rsg-spinner"></span> Loading ' + this.tabDefs[tabId].label + '…</div>'); },
    _showTableError: function (tabId, msg) { this.$el.find('#rsg-table-container').html('<div class="rsg-state-msg rsg-state-error">' + this._esc(msg) + '</div>'); },
    _renderTable: function (tabId) {
      var def = this.tabDefs[tabId], sort = this.sortState[tabId] || Object.assign({}, def.defaultSort);
      var query = (this.searchQuery[tabId] || '').toLowerCase().trim(), allRows = (this.cachedData[tabId] || []).slice();
      var rows = query ? allRows.filter(function (r) { return def.columns.some(function (col) { var v = r[col.key]; return v && String(v).toLowerCase().indexOf(query) !== -1; }); }) : allRows;
      rows.sort(function (a, b) { var av = a[sort.key], bv = b[sort.key]; if (av == null) av = sort.dir === 'asc' ? '￿' : ''; if (bv == null) bv = sort.dir === 'asc' ? '￿' : ''; if (typeof av === 'number' && typeof bv === 'number') return sort.dir === 'asc' ? av - bv : bv - av; return sort.dir === 'asc' ? String(av).localeCompare(String(bv)) : String(bv).localeCompare(String(av)); });
      var total = allRows.length, premium = allRows.reduce(function (s, r) { return s + (r._totalPremium || r.estimatedPremium || 0); }, 0);
      var active = allRows.filter(function (r) { return r.accountStatus === 'Active'; }).length;
      var urgent = allRows.filter(function (r) { return r.accountStatus === 'Urgent' || r.accountStatus === 'At Risk'; }).length;
      var selected = this.selectedIds[tabId] || {}, self = this;
      var html = '<div class="rsg-stats">';
      html += '<div class="rsg-stat"><div class="rsg-stat-label">Accounts</div><div class="rsg-stat-val rsg-accent">' + total + '</div></div>';
      html += '<div class="rsg-stat"><div class="rsg-stat-label">' + (tabId === 'prospect' ? 'Est. Premium' : 'Total Premium') + '</div><div class="rsg-stat-val">' + this._formatCurrency(premium) + '</div></div>';
      if (tabId !== 'prospect' && tabId !== 'inactive' && tabId !== 'needsReview') { html += '<div class="rsg-stat"><div class="rsg-stat-label">Active</div><div class="rsg-stat-val">' + active + '</div></div><div class="rsg-stat"><div class="rsg-stat-label">Needs Attention</div><div class="rsg-stat-val rsg-danger">' + urgent + '</div></div>'; }
      html += '</div>';
      var selCount = Object.keys(selected).length;
      html += '<div id="rsg-selection-bar" class="rsg-selection-bar" style="display:' + (selCount > 0 ? 'flex' : 'none') + '"><span id="rsg-selected-count">' + selCount + ' record' + (selCount !== 1 ? 's' : '') + ' selected</span><button id="rsg-clear-selection" class="rsg-sel-btn">&#x2715; Clear selection</button></div>';
      html += '<div class="rsg-toolbar"><div class="rsg-search-wrap"><span class="rsg-search-icon">&#x2315;</span><input id="rsg-search" type="text" placeholder="Filter ' + def.label.toLowerCase() + '…" value="' + this._esc(this.searchQuery[tabId] || '') + '" /></div><span class="rsg-count-note">' + rows.length + ' of ' + total + '</span></div>';
      var allChecked = rows.length > 0 && rows.every(function (r) { return !!selected[r.id]; });
      var someChecked = !allChecked && rows.some(function (r) { return !!selected[r.id]; });
      html += '<div class="rsg-table-wrap"><table class="rsg-table"><thead><tr>';
      html += '<th class="rsg-th-cb"><input type="checkbox" id="rsg-select-all"' + (allChecked ? ' checked' : '') + (someChecked ? ' data-indeterminate="true"' : '') + ' title="Select / deselect all" /></th>';
      def.columns.forEach(function (col) { var isSorted = sort.key === col.key; var arrow = isSorted ? (sort.dir === 'asc' ? ' ↑' : ' ↓') : ' ⇅'; var sortAttr = col.sortable !== false ? ' data-sort-key="' + col.key + '"' : ''; html += '<th' + sortAttr + ' class="' + (isSorted ? 'rsg-sorted' : '') + '">' + col.label + '<span class="rsg-arrow">' + arrow + '</span></th>'; });
      html += '</tr></thead><tbody>';
      if (rows.length === 0) { html += '<tr><td colspan="' + (def.columns.length + 1) + '"><div class="rsg-state-msg">No accounts match.</div></td></tr>'; }
      else { rows.forEach(function (r) { var isSel = !!selected[r.id]; html += '<tr class="rsg-row' + (isSel ? ' rsg-row-selected' : '') + '" data-id="' + r.id + '"><td class="rsg-td-cb"><input type="checkbox" class="rsg-row-cb" data-id="' + r.id + '"' + (isSel ? ' checked' : '') + ' /></td>'; def.columns.forEach(function (col) { html += '<td class="' + self._tdClass(col.type) + '">' + self._renderCell(r, col) + '</td>'; }); html += '</tr>'; }); }
      html += '</tbody></table></div>';
      var $container = this.$el.find('#rsg-table-container');
      $container.html(html);
      if (someChecked) $container.find('#rsg-select-all').prop('indeterminate', true);
      $container.find('.rsg-row').on('click', function () { var id = $(this).data('id'); try { sessionStorage.setItem('rsg-nav-list', JSON.stringify(rows.map(function (r) { return r.id; }))); sessionStorage.setItem('rsg-nav-tab', tabId); } catch(e) {} self.getRouter().navigate('#Account/view/' + id, { trigger: true }); });
    },
    _tdClass: function (type) { return type === 'link' ? 'rsg-td-name' : type === 'currency' ? 'rsg-td-num' : 'rsg-td-dim'; },
    _renderCell: function (r, col) { var v = r[col.key]; if (col.type === 'link') return '<a class="rsg-link" href="#Account/view/' + r.id + '" onclick="event.stopPropagation()">' + this._esc(v || '—') + '</a>'; if (col.type === 'badge') return this._badge(v); if (col.type === 'currency') return v ? this._formatCurrency(v) : '—'; if (col.type === 'date') return v ? this._formatDate(v) : '—'; if (col.type === 'lob') return v ? (Array.isArray(v) ? v.join(', ') : String(v)) : '—'; return this._esc(v || '—'); },
    _badge: function (status) { var map = {'Active':'rsg-badge-active','Urgent':'rsg-badge-urgent','Renewing':'rsg-badge-renewing','At Risk':'rsg-badge-risk','Inactive':'rsg-badge-inactive','Prospect':'rsg-badge-prospect'}; return '<span class="rsg-badge ' + (map[status] || 'rsg-badge-inactive') + '">' + this._esc(status || '—') + '</span>'; },
    _formatCurrency: function (v) { if (!v && v !== 0) return '—'; return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(v); },
    _formatDate: function (v) { if (!v) return '—'; var d = new Date(v); return isNaN(d) ? v : d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }); },
    _esc: function (s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); },
    data: function () { var tabs = [], self = this; Object.keys(this.tabDefs).forEach(function (key) { tabs.push({ key: key, label: self.tabDefs[key].label, color: self.tabDefs[key].color, active: key === self.activeTab }); }); return { tabs: tabs }; }
  });
});