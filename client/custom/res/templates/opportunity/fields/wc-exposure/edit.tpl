<div class="wc-exposure-field wc-exposure-edit">
    <table class="table table-sm wc-exposure-table">
        <thead>
            <tr>
                <th class="wc-col-code">Class Code</th>
                <th class="wc-col-desc">Description</th>
                <th class="wc-col-num">FTE</th>
                <th class="wc-col-num">PTE</th>
                <th class="wc-col-num">Payroll</th>
                <th class="wc-col-act"></th>
            </tr>
        </thead>
        <tbody>
            {{#each rows}}
            <tr class="wc-exposure-row" data-index="{{index}}">
                <td><input type="text" class="form-control" data-field="classCode" value="{{classCode}}"></td>
                <td><input type="text" class="form-control" data-field="description" value="{{description}}"></td>
                <td><input type="number" step="1" min="0" class="form-control" data-field="fte" value="{{fte}}"></td>
                <td><input type="number" step="1" min="0" class="form-control" data-field="pte" value="{{pte}}"></td>
                <td><input type="number" step="any" min="0" class="form-control" data-field="payroll" value="{{payroll}}"></td>
                <td class="wc-col-act">
                    <a role="button" tabindex="0" class="text-danger" data-action="removeRow" data-index="{{index}}" title="Remove">
                        <span class="fas fa-times"></span>
                    </a>
                </td>
            </tr>
            {{/each}}
        </tbody>
        <tfoot>
            <tr class="wc-exposure-total-row">
                <td colspan="4" class="wc-col-num"><strong>Total Location Exposure</strong></td>
                <td class="wc-col-num"><strong class="wc-exposure-total">{{totalDisplay}}</strong></td>
                <td class="wc-col-act"></td>
            </tr>
        </tfoot>
    </table>
    <button type="button" class="btn btn-default btn-sm" data-action="addRow">
        <span class="fas fa-plus fa-sm"></span> Add Class Code
    </button>
</div>
