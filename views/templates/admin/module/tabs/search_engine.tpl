<script type="text/javascript">
    var criteriaGroupToReindex{$id_search|intval} = {$groups_to_reindex|json_encode};
</script>
<div id="searchTabContainer-{$id_search|intval}" class="searchTabContainer">
    <div class="searchSort">
        <div class="search-actions-buttons">
            {as4_button text={l s='Edit' mod='pm_advancedsearch4'} href="{$base_config_url|as4_nofilter}&pm_load_function=displaySearchForm&class=AdvancedSearchClass&pm_js_callback=closeDialogIframe&id_search={$search_engine.id_search}" class='open_on_dialog_iframe' rel='980_530_1' icon_class='ui-icon ui-icon-pencil'}
            {as4_button text={l s='Visibility' mod='pm_advancedsearch4'} href="{$base_config_url|as4_nofilter}&pm_load_function=displayVisibilityForm&class=AdvancedSearchClass&pm_js_callback=closeDialogIframe&id_search={$search_engine.id_search}" class='open_on_dialog_iframe' rel='980_530_1' icon_class='ui-icon ui-icon-search'}
            {if $search_engine.active}
                {as4_button text="{l s='Status:' mod='pm_advancedsearch4'} <em id=\"searchStatusLabel{$search_engine.id_search}\">{l s='enabled' mod='pm_advancedsearch4'}</em>" title="{l s='Change status' mod='pm_advancedsearch4'}" href="{$base_config_url|as4_nofilter}&pm_load_function=processActiveSearch&id_search={$search_engine.id_search}" class="ajax_script_load status_search status_search_{$search_engine.id_search} enabled_search" icon_class='ui-icon ui-icon-circle-check'}
            {else}
                {as4_button text="{l s='Status:' mod='pm_advancedsearch4'} <em id=\"searchStatusLabel{$search_engine.id_search}\">{l s='disabled' mod='pm_advancedsearch4'}</em>" title="{l s='Change status' mod='pm_advancedsearch4'}" href="{$base_config_url|as4_nofilter}&pm_load_function=processActiveSearch&id_search={$search_engine.id_search}" class="ajax_script_load status_search status_search_{$search_engine.id_search}" icon_class='ui-icon ui-icon-circle-close'}
            {/if}
            {as4_button text={l s='Delete' mod='pm_advancedsearch4'} href="{$base_config_url|as4_nofilter}&pm_delete_obj=1&class=AdvancedSearchClass&id_search={$search_engine.id_search}" class='ajax_script_load pm_confirm' icon_class='ui-icon ui-icon-trash' title={l s='Delete item #%d ?' mod='pm_advancedsearch4' sprintf=$search_engine.id_search}}
            {as4_button text={l s='Duplicate' mod='pm_advancedsearch4'} href="{$base_config_url|as4_nofilter}&pm_duplicate_obj=1&class=AdvancedSearchClass&id_search={$search_engine.id_search}" class='ajax_script_load pm_confirm' icon_class='ui-icon ui-icon-newwin' title={l s='Duplicate item #%d ?' mod='pm_advancedsearch4' sprintf=$search_engine.id_search}}
            {as4_button text={l s='Reindex' mod='pm_advancedsearch4'} class='ajax_script_load' icon_class='ui-icon ui-icon-shuffle' onclick="reindexSearchCriterionGroups(this, criteriaGroupToReindex{$search_engine.id_search}, '#progressbarReindexSpecificSearch{$search_engine.id_search}');"}
        </div>
        <div class="progressbar_wrapper progressbarReindexSpecificSearch">
            <div class="progressbar" id="progressbarReindexSpecificSearch{$id_search|intval}"></div>
            <div class="progressbarpercent"></div>
        </div>
        {include file="../../core/clear.tpl"}
    </div>
    {include file="../../core/clear.tpl"}
    <div class="indexedCriterionGroups connectedSortableDiv" id="IndexCriterionsGroup">
        <h3 style="float:left">{l s='Active criteria groups' mod='pm_advancedsearch4'}</h3>
        <div style="float:right;">
            <abbr title="{l s='This option let you to display hidden criterions groups via a “Show more options“ link' mod='pm_advancedsearch4'}">
                {l s='Allow groups to be hidden:' mod='pm_advancedsearch4'}
            </abbr>
            <input type="checkbox" onclick="displayHideBar($(this));" name="auto_hide" value="{$id_search|intval}" />
        </div>
        {include file="../../core/clear.tpl"}

        <ul class="connectedSortable connectedSortableIndex">
        {assign var=hidden value=true}
        {foreach from=$criterions_groups_indexed item=criterions_group_indexed}
            {if $criterions_group_indexed.hidden && $hidden}
                <li class="ui-state-default ui-state-pm-separator as4-hidden-criterions-groups" id="hide_after_{$id_search|intval}">
                    <span class="ui-icon ui-icon-arrowthick-2-n-s dragIcon dragIconCriterionGroup"></span>
                    <span class="as4-criterion-group-name">{l s='Groups under this line will be hidden' mod='pm_advancedsearch4'}</span>
                    <input name="id_search" value="{$id_search|intval}" type="hidden" />
                </li>
                {assign var=hidden value=false}
            {/if}
            <li class="ui-state-default" data-id-criterion-group-unit="{$criterions_group_indexed.criterion_group_unit|escape:'htmlall':'UTF-8'}" data-id-criterion-group-type="{$criterions_group_indexed.criterion_group_type|escape:'htmlall':'UTF-8'}" id="{$criterions_group_indexed.unique_id|escape:'htmlall':'UTF-8'}" rel="{$criterions_group_indexed.id_criterion_group|intval}">
                <span class="ui-icon ui-icon-arrowthick-2-n-s dragIcon dragIconCriterionGroup"></span>
                <span class="as4-criterion-group-name">
                    {$criterions_group_indexed.name|escape:'htmlall':'UTF-8'}
                </span> 
                <span class="as4-criterion-group-label">
                    ({$criteria_group_labels[$criterions_group_indexed.criterion_group_type]|escape:'htmlall':'UTF-8'})
                    {if $criterions_group_indexed.criterion_group_type == 'category'}
                        {if !empty($criterions_group_indexed.id_criterion_group_linked)}
                            - {l s='Level' mod='pm_advancedsearch4'}&nbsp;{$criterions_group_indexed.id_criterion_group_linked|intval}
                        {else}
                            - {l s='All category levels' mod='pm_advancedsearch4'}
                        {/if}
                    {/if}
                </span>
                <span class="ui-icon ui-icon-plusthick plusIcon"></span>
                <input name="id_search" value="{$id_search|intval}" type="hidden" />
            </li>
            <script type="text/javascript">
                setCriterionGroupActions("{$criterions_group_indexed.unique_id|escape:'htmlall':'UTF-8'}", true);
            </script>
        {/foreach}
        {if $hidden}
            <li class="ui-state-default ui-state-pm-separator as4-hidden-criterions-groups" style="display:none;" id="hide_after_{$id_search|intval}">
                <span class="ui-icon ui-icon-arrowthick-2-n-s dragIcon dragIconCriterionGroup"></span>
                <span class="as4-criterion-group-name">
                    {l s='Groups under this line will be hidden' mod='pm_advancedsearch4'}
                </span>
                <input name="id_search" value="{$id_search|intval}" type="hidden" />
            </li>
        {/if}
        </ul>
        <div class="newCriterionGroupPlaceholder"></div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {
            $("input[name=auto_hide][value={$id_search|intval}]").prop("checked", {if $hidden}false{else}true{/if});
        });
    </script>

    <div class="availableCriterionGroups connectedSortableDiv" id="DesindexCriterionsGroup">
        <h3>{l s='Available criteria groups' mod='pm_advancedsearch4'}</h3>
        {foreach from=$criterions_groups key=groupType item=groupList}
            <h4>{$criterions_unit_groups_translations[$groupType]|escape:'htmlall':'UTF-8'}</h4>
            <ul class="availableCriterionGroups-{$groupType|escape:'htmlall':'UTF-8'} connectedSortable">
            {foreach from=$groupList item=criterions_group}
                <li title="{{l s='Click to add this criterion group' mod='pm_advancedsearch4'}|escape:'htmlall':'UTF-8'}" class="ui-state-default" id="{$criterions_group.unique_id|escape:'htmlall':'UTF-8'}" data-id-criterion-group-unit="{$groupType|escape:'htmlall':'UTF-8'}" data-id-criterion-group-type="{$criterions_group.type|escape:'htmlall':'UTF-8'}">
                    <span class="ui-icon ui-icon-arrowthick-2-n-s dragIcon dragIconCriterionGroup"></span>
                    <span class="as4-criterion-group-name">
                        {if !empty($criterions_group.internal_name)}
                            {$criterions_group.internal_name|escape:'htmlall':'UTF-8'}
                        {else}
                            {$criterions_group.name|escape:'htmlall':'UTF-8'}
                        {/if}
                    </span>
                    <span class="as4-criterion-group-label">({$criteria_group_labels[$criterions_group.type]|escape:'htmlall':'UTF-8'})</span>
                    <span class="ui-icon ui-icon-plusthick plusIcon"></span>
                    <input name="id_search" value="{$id_search|intval}" type="hidden" />
                </li>
            {/foreach}
            </ul>
        {/foreach}
    </div>
    {include file="../../core/clear.tpl"}

    <hr />

    <div class="seo_search_panel" id="seo_search_panel_{$id_search|intval}"></div>
    <script type="text/javascript">
        loadPanel("seo_search_panel_{$id_search|intval}", "{$base_config_url|as4_nofilter}&pm_load_function=displaySeoSearchPanelList&id_search={$id_search|intval}");
        $(".connectedSortableIndex").sortable({
            items: "> li",
            axis: "y",
            update: function(event, ui) {
                var order = $(this).sortable("toArray");
                saveOrder(order.join(","), "orderCriterionGroup", $(ui.item).children("input[name=id_search]").val(), $("input[name=auto_hide]").is(":checked"));
            }
        });
        loadAjaxLink();
    </script>
</div><!-- .searchTabContainer -->