<div class="category-tree-table-container">
    <label>{$options.label|escape:'htmlall':'UTF-8'}</label>
    <div class="margin-form">
        <div class="category-tree-table">
            <table cellpadding="5">
                <tr id="tr_categories">
                    <td colspan="2">
                    {$category_tree|as4_nofilter}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding-bottom:5px;">
                        <hr style="width:100%;" />
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
{include file='../../clear.tpl'}