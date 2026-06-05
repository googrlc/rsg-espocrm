/**
 * Workers Comp "Location & Exposure" editable grid field.
 *
 * Stores an array of class-code exposure lines as a JSON string in a text
 * column. Each line is { classCode, description, fte, pte, payroll }. The view
 * renders an add/remove row table in edit mode and a read-only table with an
 * auto-summed Total Payroll in detail mode.
 */
define('custom:views/opportunity/fields/wc-exposure', ['views/fields/base'], function (Dep) {

    return Dep.extend({

        detailTemplate: 'custom:opportunity/fields/wc-exposure/detail',
        editTemplate: 'custom:opportunity/fields/wc-exposure/edit',
        listTemplate: 'custom:opportunity/fields/wc-exposure/list',

        events: {
            'click [data-action="addRow"]': function (e) {
                e.preventDefault();
                this.workingRows = this.readRowsFromDom(true);
                this.workingRows.push(this.emptyRow());
                this.reRender();
            },
            'click [data-action="removeRow"]': function (e) {
                e.preventDefault();
                var index = parseInt(e.currentTarget.getAttribute('data-index'), 10);
                this.workingRows = this.readRowsFromDom(true);
                this.workingRows.splice(index, 1);
                if (this.workingRows.length === 0) {
                    this.workingRows.push(this.emptyRow());
                }
                this.reRender();
            },
            'input [data-field="payroll"]': function () {
                this.updateTotal();
            }
        },

        setMode: function (mode) {
            // Re-seed the working copy from the model every time edit starts;
            // clearing it on any non-edit mode covers save and cancel.
            if (mode !== this.MODE_EDIT) {
                this.workingRows = null;
            }

            return Dep.prototype.setMode.call(this, mode);
        },

        data: function () {
            var rows = this.getWorkingRows();
            var total = 0;

            var viewRows = rows.map(function (r, i) {
                var payrollNum = this.toNumber(r.payroll);

                if (payrollNum !== null) {
                    total += payrollNum;
                }

                return {
                    index: i,
                    classCode: r.classCode || '',
                    description: r.description || '',
                    fte: this.blankIfNull(r.fte),
                    pte: this.blankIfNull(r.pte),
                    payroll: this.blankIfNull(r.payroll),
                    payrollDisplay: payrollNum === null ? '' : this.formatCurrency(payrollNum)
                };
            }, this);

            var data = Dep.prototype.data.call(this);

            data.rows = viewRows;
            data.isEmpty = viewRows.length === 0;
            data.rowCount = viewRows.length;
            data.countLabel = viewRows.length + ' class code' + (viewRows.length === 1 ? '' : 's');
            data.totalDisplay = this.formatCurrency(total);

            return data;
        },

        getWorkingRows: function () {
            if (this.isEditMode()) {
                if (!this.workingRows) {
                    this.workingRows = this.parseValue(this.model.get(this.name));

                    if (this.workingRows.length === 0) {
                        this.workingRows = [this.emptyRow()];
                    }
                }

                return this.workingRows;
            }

            return this.parseValue(this.model.get(this.name));
        },

        readRowsFromDom: function (keepEmpty) {
            var rows = [];
            var $rows = this.$el.find('tr.wc-exposure-row');

            $rows.each(function (i) {
                var $tr = $rows.eq(i);

                var row = {
                    classCode: (($tr.find('[data-field="classCode"]').val()) || '').trim(),
                    description: (($tr.find('[data-field="description"]').val()) || '').trim(),
                    fte: (($tr.find('[data-field="fte"]').val()) || '').trim(),
                    pte: (($tr.find('[data-field="pte"]').val()) || '').trim(),
                    payroll: (($tr.find('[data-field="payroll"]').val()) || '').trim()
                };

                var hasData = row.classCode || row.description || row.fte || row.pte || row.payroll;

                if (keepEmpty || hasData) {
                    rows.push(row);
                }
            });

            return rows;
        },

        updateTotal: function () {
            var total = 0;

            this.readRowsFromDom(true).forEach(function (r) {
                var n = parseFloat(r.payroll);

                if (!isNaN(n)) {
                    total += n;
                }
            });

            this.$el.find('.wc-exposure-total').text(this.formatCurrency(total));
        },

        fetch: function () {
            var data = {};
            var rows = this.readRowsFromDom(false);

            data[this.name] = rows.length ? JSON.stringify(rows) : null;

            return data;
        },

        // When the field is required (Workers Comp line of business) at least one
        // row with a Class Code must be present. Additional rows are optional.
        validateRequired: function () {
            if (!this.isRequired()) {
                return false;
            }

            var hasClassCode = this.parseValue(this.model.get(this.name)).some(function (row) {
                return (row.classCode || '').trim() !== '';
            });

            if (!hasClassCode) {
                this.showValidationMessage('At least one Class Code row is required.');

                return true;
            }

            return false;
        },

        parseValue: function (raw) {
            if (!raw) {
                return [];
            }

            if (Array.isArray(raw)) {
                return raw.slice();
            }

            try {
                var arr = JSON.parse(raw);

                return Array.isArray(arr) ? arr : [];
            } catch (e) {
                return [];
            }
        },

        emptyRow: function () {
            return {classCode: '', description: '', fte: '', pte: '', payroll: ''};
        },

        toNumber: function (v) {
            if (v === '' || v === null || v === undefined) {
                return null;
            }

            var n = parseFloat(v);

            return isNaN(n) ? null : n;
        },

        blankIfNull: function (v) {
            return (v === null || v === undefined) ? '' : v;
        },

        formatCurrency: function (n) {
            var num = parseFloat(n);

            if (isNaN(num)) {
                num = 0;
            }

            return '$' + num.toLocaleString('en-US', {maximumFractionDigits: 0});
        }
    });
});
