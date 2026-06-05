{{#if isEmpty}}
<span class="text-muted">No class codes</span>
{{else}}
<div class="wc-exposure-field">
    <table class="table table-sm wc-exposure-table wc-exposure-detail">
        <thead>
            <tr>
                <th class="wc-col-code">Class Code</th>
                <th class="wc-col-desc">Description</th>
                <th class="wc-col-num">FTE</th>
                <th class="wc-col-num">PTE</th>
                <th class="wc-col-num">Payroll</th>
            </tr>
        </thead>
        <tbody>
            {{#each rows}}
            <tr>
                <td>{{classCode}}</td>
                <td>{{description}}</td>
                <td class="wc-col-num">{{fte}}</td>
                <td class="wc-col-num">{{pte}}</td>
                <td class="wc-col-num">{{payrollDisplay}}</td>
            </tr>
            {{/each}}
        </tbody>
        <tfoot>
            <tr class="wc-exposure-total-row">
                <td colspan="4" class="wc-col-num"><strong>Total Location Exposure</strong></td>
                <td class="wc-col-num"><strong class="wc-exposure-total">{{totalDisplay}}</strong></td>
            </tr>
        </tfoot>
    </table>
</div>
{{/if}}
