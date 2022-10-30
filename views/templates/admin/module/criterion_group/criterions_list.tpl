{if $pm_load_function != 'displaySortCriteriaPanel'}
    <div id="sortCriteriaPanel">
{/if}

{include file="../../core/clear.tpl"}
{if $auto_sync_active_status}
    {module->showInfo text="{l s='Be aware that auto synchronize object status with criteria is enabled into the module configuration. You will not be able to change the criteria status here.' mod='pm_advancedsearch4'}"}
{/if}

<div class="criterionGroupActions">
    <ul>
        <li>
            <a href="{$base_config_url|as4_nofilter}&pm_load_function=processEnableAllCriterions&id_criterion_group={$criterion_group->id|intval}&id_search={$criterion_group->id_search|intval}"
                class="ajax_script_load enableAllCriterions {if $auto_sync_active_status} disabledAction{/if}"
                title="{l s='Enable all criteria' mod='pm_advancedsearch4'}"
            >
                <i class="material-icons">done_all</i> {l s='Enable all criteria' mod='pm_advancedsearch4'}
            </a>
        </li>
        <li>
            <a href="{$base_config_url|as4_nofilter}&pm_load_function=processDisableAllCriterions&id_criterion_group={$criterion_group->id|intval}&id_search={$criterion_group->id_search|intval}"
                class="ajax_script_load disableAllCriterions {if $auto_sync_active_status} disabledAction{/if}"
                title="{l s='Disable all criteria' mod='pm_advancedsearch4'}"
            >
                <i class="material-icons">close</i>{l s='Disable all criteria' mod='pm_advancedsearch4'}
            </a>
        </li>
    </ul>
</div>
{include file="../../core/clear.tpl"}

<table class="criterionsList">
    <thead>
        <th colspan="2">{l s='Label' mod='pm_advancedsearch4'}</th>
        {if $supportsImageCriterionGroup && $criterion_group->display_type == 2}
            <th>{l s='Image' mod='pm_advancedsearch4'}</th>
        {/if}
        {if $has_custom_criterions}
        <th>{l s='Link to a custom criterion' mod='pm_advancedsearch4'}</th>
        {/if}
        <th>{l s='Actions' mod='pm_advancedsearch4'}</th>
    </thead>
    <tbody>
        {foreach from=$criterions item=criterion}
        <tr id="criterion_{$criterion.id_criterion|intval}">
            <td{if $criterion_group->sort_by == 'position'} class="dragIcon dragIconCriterion"{/if}>
                <i class="material-icons" style="{if $criterion_group->sort_by != 'position'}visibility:hidden{/if}">swap_vert</i>
            </td>
            <td>
                {if empty($criterion.is_custom)}
                    {$criterion.value|escape:'htmlall':'UTF-8'}
                    {if $criterion_group->criterion_group_type == 'category'}
                        {strip}
                        (
                        {if $criterion.level_depth > 0}
                            {l s='parent:' mod='pm_advancedsearch4'} {$criterion.parent_name|escape:'htmlall':'UTF-8'}
                        {/if}
                        {if $criterion.level_depth > 0 && $criterion_group->id_criterion_group_linked == 0} - {/if}
                        {if $criterion_group->id_criterion_group_linked == 0}
                            {l s='level:' mod='pm_advancedsearch4'} {if $criterion.level_depth > 0}{($criterion.level_depth|intval - 1)}{else}{$criterion.level_depth|intval}{/if}
                        {/if}
                        )
                        {/strip}
                    {/if}
                {else}
                    <div class="criterionCustomLiveEditContainer" data-id-criterion="{$criterion.id_criterion|intval}" data-id-search="{$criterion_group->id_search|intval}">
                        {if $is_color_group}
                            <div class="criterionCustomLiveField">
                                {as4_inputColor obj=$criterion.obj key='color' label={l s='Color:' mod='pm_advancedsearch4'}}
                            </div>
                        {/if}
                        <div class="criterionCustomLiveField">
                            {as4_inputTextLang obj=$criterion.obj key='value'}
                        </div>
                        {module->displaySubmit text="{l s='Save' d='Admin.Actions'}" name='submitCustomCriterionForm'}
                    </div>
                {/if}
            </td>
            {if $supportsImageCriterionGroup && $criterion_group->display_type == 2}
                <td class="criterionImageTd">
                    <div class="criterionImageContainer">
                        <form action="{$base_config_url|as4_nofilter}" method="post" enctype="multipart/form-data" target="dialogIframePostForm">
                            {as4_inlineUploadFile obj=$criterion.obj key="icon{$criterion.id_criterion|intval}" key_db='icon' destination='/search_files/criterions/'}
                            <input name="id_search" value="{$criterion_group->id_search|intval}" type="hidden" />
                            <input name="id_criterion" value="{$criterion.id_criterion|intval}" type="hidden" />
                            <input name="key_criterions_group" value="{$criterion_group->criterion_group_type|escape:'htmlall':'UTF-8'}-{$criterion_group->id_criterion_group_linked|intval}-{$criterion_group->id_search|intval}" type="hidden" />
                        </form>
                    </div>
                </td>
            {/if}
            {if $has_custom_criterions}
                <td class="criterionCustomTd">
                    {if empty($criterion.is_custom)}
                        {if $criterion.custom_criterions_list|is_array && sizeof($criterion.custom_criterions_list) > 1}
                            <div class="addCriterionToCustomGroupContainer">
                                {as4_select obj=$criterion.custom_criterions_obj options=$criterion.custom_criterions_list key="custom_group_link_id_{$criterion.id_criterion|intval}" defaultvalue=false onchange="processAddCustomCriterionToGroup($(this), {$criterion_group->id_search|intval}, {$criterion_group->id|intval})"}
                            </div>
                        {/if}
                    {/if}
                </td>
            {/if}
            <td>
                {if !empty($criterion.is_custom)}
                    <div class="criterionActions">
                        {strip}
                        <a href="{$base_config_url|as4_nofilter}&pm_load_function=processActiveCriterion&id_criterion={$criterion.id_criterion|intval}&id_search={$criterion_group->id_search|intval}" class="ajax_script_load {if !$auto_sync_active_status}activeCriterion{else} disabledAction{/if}">
                            <i id="imgActiveCriterion{$criterion.id_criterion|intval}" class="material-icons" data-current-mi-icon="{if $criterion.visible}done{else}close{/if}">{if $criterion.visible}done{else}close{/if}</i>
                        </a>
                        &nbsp;
                        <a href="{$base_config_url|as4_nofilter}&pm_load_function=processDeleteCustomCriterion&id_criterion={$criterion.id_criterion|intval}&id_search={$criterion_group->id_search|intval}" class="ajax_script_load pm_confirm deleteCustomCriterion" title="{l s='Do you really want to delete this custom criterion ?' mod='pm_advancedsearch4'}">
                            <i id="imgDeleteCriterion{$criterion.id_criterion|intval}" class="material-icons" data-current-mi-icon="delete">delete</i>
                        </a>
                        {/strip}
                    </div>
                {elseif empty($criterion.is_custom)}
                    <div class="criterionActions">
                        <a href="{$base_config_url|as4_nofilter}&pm_load_function=processActiveCriterion&id_criterion={$criterion.id_criterion|intval}&id_search={$criterion_group->id_search|intval}" class="ajax_script_load {if !$auto_sync_active_status}activeCriterion{else} disabledAction{/if}">
                            <i id="imgActiveCriterion{$criterion.id_criterion|intval}" class="material-icons" data-current-mi-icon="{if $criterion.visible}done{else}close{/if}">{if $criterion.visible}done{else}close{/if}</i>
                        </a>
                    </div>
                {else}
                    <div class="criterionActions">
                        <a href="{$base_config_url|as4_nofilter}&pm_load_function=processActiveCriterion&id_criterion={$criterion.id_criterion|intval}&id_search={$criterion_group->id_search|intval}" class="ajax_script_load {if !$auto_sync_active_status}activeCriterion{else} disabledAction{/if}">
                            <i id="imgActiveCriterion{$criterion.id_criterion|intval}" class="material-icons" data-current-mi-icon="{if $criterion.visible}done{else}close{/if}">{if $criterion.visible}done{else}close{/if}</i>
                        </a>
                    </div>
                {/if}
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>

{if $criterion_group->sort_by == 'position'}
    <script type="text/javascript">
        $("table.criterionsList tbody").sortable({
            axis: 'y',
            handle : '.dragIconCriterion',
            helper: function(e, ui) {
                ui.children().each(function() {
                    $(this).width($(this).outerWidth(true));
                });
                return ui;
            },
            update: function(event, ui) {
                var order = $(this).sortable('toArray');
                saveOrder(order.join(","), 'orderCriterion', {$criterion_group->id_search|intval});
            }
        });
    </script>
{/if}

{if $pm_load_function != 'displaySortCriteriaPanel'}
    </div>
{/if}
