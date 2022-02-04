<script src="{$module_path|as4_nofilter}views/js/treeview/jquery.treeview.js" type="text/javascript"></script>
<script src="{$module_path|as4_nofilter}views/js/treeview/jquery.treeview.async.js" type="text/javascript"></script>
<script src="{$module_path|as4_nofilter}views/js/treeview/jquery.treeview.edit.js" type="text/javascript"></script>
<script src="{$module_path|as4_nofilter}views/js/admin-categories-tree.js" type="text/javascript"></script>
<link type="text/css" rel="stylesheet" href="{$module_path|as4_nofilter}views/css/treeview/jquery.treeview.css" />

{foreach from=$hidden_selected_categories item=id_category}
    <input type="hidden" name="{$input_name|escape:'htmlall':'UTF-8'}" value="{$id_category|intval}" />
{/foreach}
         
<script type="text/javascript">
    checkAllChildrenLabel = {{l s='Check all children' mod='pm_advancedsearch4'}|json_encode};
    loadTreeView({$input_name|json_encode}, {$category_root_id|intval}, {$selected_cat_js|json_encode}, {{l s='selected' mod='pm_advancedsearch4'}|json_encode}, {$root_category_name|json_encode});
</script>

<div class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <a rel="{$input_name|escape:'htmlall':'UTF-8'}" href="#" id="collapse_all-{$input_selector_value|escape:'htmlall':'UTF-8'}" class="ui-button ui-widget ui-state-default ui-button-text-only ui-corner-all" style="padding:3px;">{l s='Collapse All' mod='pm_advancedsearch4'}</a> &nbsp;
    <a rel="{$input_name|escape:'htmlall':'UTF-8'}" href="#" id="expand_all-{$input_selector_value|escape:'htmlall':'UTF-8'}" class="ui-button ui-widget ui-state-default ui-button-text-only  ui-corner-all" style="padding:3px;">{l s='Expand All' mod='pm_advancedsearch4'}</a> &nbsp;
    {if empty($use_radio)}
        <a href="#" rel="{$input_name|escape:'htmlall':'UTF-8'}" id="check_all-{$input_selector_value|escape:'htmlall':'UTF-8'}" class="ui-button ui-widget ui-state-default ui-button-text-only ui-corner-all" style="padding:3px;">{l s='Check All' mod='pm_advancedsearch4'}</a> &nbsp;
        <a href="#" rel="{$input_name|escape:'htmlall':'UTF-8'}" id="uncheck_all-{$input_selector_value|escape:'htmlall':'UTF-8'}" class="ui-button ui-widget ui-state-default ui-button-text-only ui-corner-all" style="padding:3px;">{l s='Uncheck All' mod='pm_advancedsearch4'}</a>
    {/if}
</div>

<ul id="categories-treeview-{$input_selector_value|escape:'htmlall':'UTF-8'}" class="filetree">
    <li id="{$category_root_id|intval}-{$input_selector_value|escape:'htmlall':'UTF-8'}" class="{$input_selector_value|escape:'htmlall':'UTF-8'} hasChildren">
        <span class="folder">
            <input type="{if empty($use_radio)}checkbox{else}radio{/if}" name="{$input_name|escape:'htmlall':'UTF-8'}" value="{$category_root_id|intval}" {if !empty($root_is_selected)}checked{/if} onclick="clickOnCategoryBox($(this), '{$input_name|escape:'htmlall':'UTF-8'}');" /> {$root_category_name|escape:'htmlall':'UTF-8'}
        </span>
        <ul>
            <li>
                <span class="placeholder">&nbsp;</span>
            </li>
        </ul>
    </li>
</ul>