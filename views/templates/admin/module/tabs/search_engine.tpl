<script type="text/javascript">
    var criteriaGroupToReindex{$id_search|intval} = {$groups_to_reindex|json_encode};
</script>
<div id="searchTabContainer-{$id_search|intval}" class="searchTabContainer">
    <div class="searchSort">
        <div class="search-actions-buttons">
            {as4_button text={l s='Edit' mod='pm_advancedsearch4'} href="{$base_config_url|as4_nofilter}&pm_load_function=displaySearchForm&class=Models\Search&pm_js_callback=closeDialogIframe&id_search={$search_engine.id_search}" class='open_on_dialog_iframe' rel='980_530_1' icon_class='edit'}
            {as4_button text={l s='Visibility' mod='pm_advancedsearch4'} href="{$base_config_url|as4_nofilter}&pm_load_function=displayVisibilityForm&class=Models\Search&pm_js_callback=closeDialogIframe&id_search={$search_engine.id_search}" class='open_on_dialog_iframe' rel='980_530_1' icon_class='search'}
            {if $search_engine.active}
                {as4_button text="{l s='Status:' mod='pm_advancedsearch4'} <em id=\"searchStatusLabel{$search_engine.id_search}\">{l s='enabled' mod='pm_advancedsearch4'}</em>" title="{l s='Change status' mod='pm_advancedsearch4'}" href="{$base_config_url|as4_nofilter}&pm_load_function=processActiveSearch&id_search={$search_engine.id_search}" class="ajax_script_load status_search status_search_{$search_engine.id_search} enabled_search" icon_class='check_circle'}
            {else}
                {as4_button text="{l s='Status:' mod='pm_advancedsearch4'} <em id=\"searchStatusLabel{$search_engine.id_search}\">{l s='disabled' mod='pm_advancedsearch4'}</em>" title="{l s='Change status' mod='pm_advancedsearch4'}" href="{$base_config_url|as4_nofilter}&pm_load_function=processActiveSearch&id_search={$search_engine.id_search}" class="ajax_script_load status_search status_search_{$search_engine.id_search}" icon_class='cancel'}
            {/if}
            {as4_button text={l s='Delete' mod='pm_advancedsearch4'} href="{$base_config_url|as4_nofilter}&pm_delete_obj=1&class=Models\Search&id_search={$search_engine.id_search}" class='ajax_script_load pm_confirm btn-danger' icon_class='delete' title={l s='Delete item #%d ?' mod='pm_advancedsearch4' sprintf=$search_engine.id_search}}
            {as4_button text={l s='Duplicate' mod='pm_advancedsearch4'} href="{$base_config_url|as4_nofilter}&pm_duplicate_obj=1&class=Models\Search&id_search={$search_engine.id_search}" class='ajax_script_load pm_confirm' icon_class='content_copy' title={l s='Duplicate item #%d ?' mod='pm_advancedsearch4' sprintf=$search_engine.id_search}}
            {as4_button text={l s='Reindex' mod='pm_advancedsearch4'} class='ajax_script_load' icon_class='build' onclick="reindexSearchCriterionGroups(this, criteriaGroupToReindex{$search_engine.id_search}, '#progressbarReindexSpecificSearch{$search_engine.id_search}', '{l s='Indexation done' mod='pm_advancedsearch4'}');"}
        </div>
        <div class="progressbar_wrapper progressbarReindexSpecificSearch">
            <div class="progressbar" id="progressbarReindexSpecificSearch{$id_search|intval}"></div>
            <div class="progressbarpercent"></div>
        </div>
        {include file="../../core/clear.tpl"}
    </div>
    {include file="../../core/clear.tpl"}
    <div class="connectedSortableContainer">
        <div class="availableCriterionGroups connectedSortableDiv" id="DesindexCriterionsGroup">
            <h3>{l s='Available criteria groups' mod='pm_advancedsearch4'}</h3>
            {foreach from=$criterions_groups key=groupType item=groupList}
                <h4>{$criterions_unit_groups_translations[$groupType]|escape:'htmlall':'UTF-8'}</h4>
                <ul class="availableCriterionGroups-{$groupType|escape:'htmlall':'UTF-8'} connectedSortable">
                {foreach from=$groupList item=criterions_group}
                    <li title="{l s='Click to add this criteria group' mod='pm_advancedsearch4'}" class="ui-state-default" id="{$criterions_group.unique_id|escape:'htmlall':'UTF-8'}" data-id-criterion-group-unit="{$groupType|escape:'htmlall':'UTF-8'}" data-id-criterion-group-type="{$criterions_group.type|escape:'htmlall':'UTF-8'}">
                        <i class="material-icons dragIcon dragIconCriterionGroup">unfold_more</i>
                        <span class="as4-criterion-group-name">
                            {if !empty($criterions_group.internal_name)}
                                {$criterions_group.internal_name|escape:'htmlall':'UTF-8'}
                            {else}
                                {$criterions_group.name|escape:'htmlall':'UTF-8'}
                            {/if}
                        </span>
                        <span class="as4-criterion-group-label">({$criteria_group_labels[$criterions_group.type]|escape:'htmlall':'UTF-8'})</span>
                        <div class="plusIconContainer">
                            <i class="material-icons plusIcon">add</i>
                        </div>
                        <input name="id_search" value="{$id_search|intval}" type="hidden" />
                    </li>
                {/foreach}
                </ul>
            {/foreach}
        </div>
        <div class="indexedCriterionGroups connectedSortableDiv" id="IndexCriterionsGroup">
            <h3 style="float:left">{l s='Active criteria groups' mod='pm_advancedsearch4'}</h3>
            <div style="float:right;{if version_compare($smarty.const._PS_VERSION_, '1.7.7.0', '<')}display:flex;align-items:center;{/if}">
                <abbr
                    title="{l s='This option allows you to hide certain groups of criteria, and allow them to be displayed only after clicking on "Show more options".' mod='pm_advancedsearch4'}">
                    {l s='Allow groups to be hidden:' mod='pm_advancedsearch4'}
                </abbr>

                <span class="switch prestashop-switch fixed-width-lg" style="display: inline-block; width: {if version_compare($smarty.const._PS_VERSION_, '1.7.7.0', '<')}80{else}40{/if}px !important;">
                    <input type="radio" name="auto_hide_{$id_search|intval}" id="auto_hide_{$id_search|intval}_on" value="{$id_search|intval}" onclick="displayHideBar($(this), {$id_search|intval});" />
                    <label for="auto_hide_{$id_search|intval}_on" class="radioCheck">{if version_compare($smarty.const._PS_VERSION_, '1.7.7.0', '<')}{l s='Yes' d='Admin.Global'}{/if}</label>
                    <input type="radio" name="auto_hide_{$id_search|intval}" id="auto_hide_{$id_search|intval}_off" value="0" onclick="displayHideBar($(this), {$id_search|intval});" />
                    <label for="auto_hide_{$id_search|intval}_off" class="radioCheck">{if version_compare($smarty.const._PS_VERSION_, '1.7.7.0', '<')}{l s='No' d='Admin.Global'}{/if}</label>
                    <a class="slide-button btn"></a>
                </span>
            </div>
            {include file="../../core/clear.tpl"}

            <ul class="connectedSortable connectedSortableIndex">
                {assign var=hidden value=true}
                {foreach from=$criterions_groups_indexed item=criterions_group_indexed}
                    {if $criterions_group_indexed.hidden && $hidden}
                        <li class="ui-state-default ui-state-pm-separator as4-hidden-criterions-groups"
                            id="hide_after_{$id_search|intval}">
                            <i class="material-icons dragIcon dragIconCriterionGroup">unfold_more</i>
                            <span
                                class="as4-criterion-group-name" style="margin:0 auto">{l s='Groups under this line will be hidden' mod='pm_advancedsearch4'}</span>
                            <input name="id_search" value="{$id_search|intval}" type="hidden" />
                        </li>
                        {assign var=hidden value=false}
                    {/if}
                    <li class="ui-state-default"
                        data-id-criterion-group-unit="{$criterions_group_indexed.criterion_group_unit|escape:'htmlall':'UTF-8'}"
                        data-id-criterion-group-type="{$criterions_group_indexed.criterion_group_type|escape:'htmlall':'UTF-8'}"
                        id="{$criterions_group_indexed.unique_id|escape:'htmlall':'UTF-8'}"
                        rel="{$criterions_group_indexed.id_criterion_group|intval}">
                        <i class="material-icons dragIcon dragIconCriterionGroup">unfold_more</i>
                        <span class="as4-criterion-group-name">
                            {$criterions_group_indexed.name|escape:'htmlall':'UTF-8'}
                        </span>
                        <span class="as4-criterion-group-label">
                            ({$criteria_group_labels[$criterions_group_indexed.criterion_group_type]|escape:'htmlall':'UTF-8'})
                            {if $criterions_group_indexed.criterion_group_type == 'category'}
                                {if !empty($criterions_group_indexed.id_criterion_group_linked)}
                                    -
                                    {l s='Level' mod='pm_advancedsearch4'}&nbsp;{$criterions_group_indexed.id_criterion_group_linked|intval}
                                {else}
                                    - {l s='All category levels' mod='pm_advancedsearch4'}
                                {/if}
                            {/if}
                        </span>
                        <div class="plusIconContainer">
                            <i class="material-icons plusIcon">add</i>
                        </div>
                        <input name="id_search" value="{$id_search|intval}" type="hidden" />
                    </li>
                    <script type="text/javascript">
                        setCriterionGroupActions("{$criterions_group_indexed.unique_id|escape:'htmlall':'UTF-8'}", true);
                    </script>
                {/foreach}
                {if $hidden}
                    <li class="ui-state-default ui-state-pm-separator as4-hidden-criterions-groups" style="display:none;"
                        id="hide_after_{$id_search|intval}">
                        <i class="material-icons dragIcon dragIconCriterionGroup">unfold_more</i>
                        <span class="as4-criterion-group-name" style="margin:0 auto">
                            {l s='Groups under this line will be hidden' mod='pm_advancedsearch4'}
                        </span>
                        <input name="id_search" value="{$id_search|intval}" type="hidden" />
                    </li>
                {/if}
            </ul>
            <div class="newCriterionGroupPlaceholder"></div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            {if $hidden}
                $("input#auto_hide_{$id_search|intval}_off").prop("checked", true);
            {else}
                $("input#auto_hide_{$id_search|intval}_on").prop("checked", true);
            {/if}
        });
    </script>
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
                saveOrder(order.join(","), "orderCriterionGroup", {$id_search|intval});
            }
        });
        loadAjaxLink();
    </script>
</div><!-- .searchTabContainer -->
