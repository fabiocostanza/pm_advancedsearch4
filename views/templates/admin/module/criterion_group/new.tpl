{as4_startForm id="criteriaGroupOptions_{$params.obj->id}" obj=$params.obj params=$params}

{module->_displayTitle text="{l s='Criterion group settings' mod='pm_advancedsearch4'}"}

{if $params.obj->criterion_group_type == 'category'}
    {if !empty($params.obj->id_criterion_group_linked)}
        {module->_displaySubTitle text="{l s='Type:' mod='pm_advancedsearch4'} {l s='Category (level %d)' sprintf=$params.obj->id_criterion_group_linked mod='pm_advancedsearch4'}"}
    {else}
        {module->_displaySubTitle text="{l s='Type:' mod='pm_advancedsearch4'} {l s='Category (all level)' mod='pm_advancedsearch4'}"}
    {/if}
{else}
    {module->_displaySubTitle text="{l s='Type:' mod='pm_advancedsearch4'} {$criteria_group_labels[$params.obj->criterion_group_type]|ucfirst}"}
{/if}

{if $params.obj->criterion_group_type == 'subcategory'}
    {module->_showInfo text="
        {l s='This criterion group will only be available on categories and products pages.' mod='pm_advancedsearch4'}<br /><br />
        {l s='Only subcategories of the base URL (context) will be shown.' mod='pm_advancedsearch4'}
    "}
{/if}

{as4_inputTextLang obj=$params.obj key='name' label={l s='Public name' mod='pm_advancedsearch4'}}
{as4_select obj=$params.obj options=$display_type label={l s='Display method' mod='pm_advancedsearch4'} key='display_type' defaultvalue=false onchange="showRelatedOptions($(this), '{$params.obj->criterion_group_type}');"}

{as4_inputFileLang obj=$params.obj key='icon' label={l s='Icon' mod='pm_advancedsearch4'} destination='/search_files/criterions_group/' required=false extend=true tips={l s='You can upload a picture from your hard disk. This picture can be different for each language.' mod='pm_advancedsearch4'}}

{if $params.obj->criterion_group_type == 'category'}
    <div class="blc_category_tree_options {if is_array($context_type) && sizeof($context_type) == 1}as4-hidden{/if}">
        {as4_select obj=$params.obj options=$context_type label={l s='Starting level' mod='pm_advancedsearch4'} key='context_type' defaultvalue=false}
    </div>
    <div class="blc_category_options">
        {if empty($params.obj->id_criterion_group_linked)}
            <input name="show_all_depth" value="{$params.obj->show_all_depth|intval}" type="hidden" />
            {*
            {as4_inputActive obj=$params.obj key_active='show_all_depth' key_db='show_all_depth' label={l s='Show all depth' mod='pm_advancedsearch4'}}
            *}
        {else}
            {as4_inputActive obj=$params.obj key_active='only_children' key_db='only_children' label={l s='Show only subcategories related to the previously selected criterions' mod='pm_advancedsearch4'}}
        {/if}
    </div>
{/if}

<div class="all_label" style="display:none">
    {as4_inputTextLang obj=$params.obj key='all_label' label={l s='Label for “All“ choice' mod='pm_advancedsearch4'}}
</div>

<div class="blc_range">
    {as4_inputActive obj=$params.obj key_active='range' key_db='range' label={l s='Display as range' mod='pm_advancedsearch4'} onclick="displayRangeOptions($(this), '{$params.obj->criterion_group_type}');"}
</div>

<div class="blc_range_nb" style="display:none">
    {as4_inputText obj=$params.obj key='range_nb' label={l s='Step' mod='pm_advancedsearch4'} onchange='convertToPointDecimal($(this));'}
</div>

<div class="blc_range_interval" style="display:none">
    {as4_inputTextLang obj=$params.obj key='range_interval' label={l s='Range (separated by comma)' mod='pm_advancedsearch4'} placeholder="0,50,100,150,200,500,1000"}
</div>

<div class="blc_range_sign" style="display:none">
    {as4_inputTextLang obj=$params.obj key='range_sign' label={l s='Unit' mod='pm_advancedsearch4'}}
</div>

<div class="blc_with_search_area">
    {as4_inputActive obj=$params.obj key_active='filter_option' key_db='filter_option' label={l s='Display input text in order to filter values' mod='pm_advancedsearch4'}}
</div>

<div class="multicrit" style="display:none">
    {as4_inputActive obj=$params.obj key_active='is_multicriteria' key_db='is_multicriteria' label={l s='Allow multiple choice' mod='pm_advancedsearch4'} onclick="showRelatedOptions($('#display_type'), '{$params.obj->criterion_group_type}');"}
    <div class="combined_criterion" style="display:none">
        {as4_inputActive obj=$params.obj key_active='is_combined' key_db='is_combined' label={l s='Operator between criterions' mod='pm_advancedsearch4'} on_label={l s='AND' mod='pm_advancedsearch4'} off_label={l s='OR' mod='pm_advancedsearch4'}}
    </div>
</div>

{if !$search_engine->id_hook|in_array:$display_vertical_search_block}
    {as4_inputText obj=$params.obj key='css_classes' label={l s='CSS classes to apply' mod='pm_advancedsearch4'}}
{/if}

{if $search_engine->show_hide_crit_method == 3}
    <div class="overflow_height_container">
        {as4_inputText obj=$params.obj key='overflow_height' label={l s='Overflow height (in px)' mod='pm_advancedsearch4'}}
    </div>
{/if}

{if $search_engine->show_hide_crit_method == 1 || $search_engine->show_hide_crit_method == 2}
    <div class="max_display_container" style="display:none">
        {as4_inputText obj=$params.obj key='max_display' label={l s='Maximum number of criteria to display (0 = unlimited)' mod='pm_advancedsearch4'}}
    </div>
{/if}

<input name="id_search" value="{$params.obj->id_search|intval}" type="hidden" />
<input name="id_criterion_group" value="{$params.obj->id|intval}" type="hidden" />

{module->_displaySubmit text="{l s='Save' mod='pm_advancedsearch4'}" name='submitCriteriaGroupOptions'}

<script type="text/javascript">
    $(document).ready(function () {
        $("#range_on:checked").trigger("click");
        $("#range_off:checked").trigger("click");
        $("#display_type").trigger("change");
        loadAjaxLink();
    });
</script>

{if $params.obj->criterion_group_type|in_array:$sortable_criterion_group}
    <div class="custom_criteria_container">
        <hr />
        {module->_displayTitle text="{l s='Create a new custom criterion' mod='pm_advancedsearch4'}"}
        <div id="addCustomCriterionContainer" data-id-search="{$params.obj->id_search|intval}" data-id-criterion-group="{$params.obj->id|intval}">
            {as4_inputTextLang obj=$new_custom_criterion key='value' label={l s='Criterion label:' mod='pm_advancedsearch4'}}
            {if $is_color_group}
                {as4_inputColor obj=$new_custom_criterion key='color' label={l s='Color:' mod='pm_advancedsearch4'}}
            {/if}
            <center>
                {module->_displaySubmit text="{l s='Add custom criterion' mod='pm_advancedsearch4'}" name='submitAddCustomCriterionForm'}
            </center>
        </div>
        <hr />
    </div><!-- .custom_criteria_container -->

    <div class="sort_criteria_container">
        {module->_displayTitle text="{l s='Sort criteria' mod='pm_advancedsearch4'}"}
        <div style="width:380px;float:left;">
            <span style="float:left;line-height:25px;">
                <b><em>{l s='Apply specific sort order:' mod='pm_advancedsearch4'}</em></b> &nbsp; &nbsp; 
            </span> 
            {as4_select obj=$params.obj options=$criterions_sort_by label=false key='sort_by' defaultvalue=0 onchange="reorderCriteria($('#sort_by').val(), $('#sort_way').val(), $('input[name=\'id_criterion_group\']').val(), {$params.obj->id_search|intval});"}
        </div>
        <div style="width:250px;float:left;">
            {as4_select obj=$params.obj options=$criterions_sort_way label=false key='sort_way' defaultvalue=0 onchange="reorderCriteria($('#sort_by').val(), $('#sort_way').val(), $('input[name=\'id_criterion_group\']').val(), {$params.obj->id_search|intval});"}
        </div>

        {if !empty($criterions_list_rendered)}
            {$criterions_list_rendered|as4_nofilter}
        {/if}

        {include file="../../core/clear.tpl"}
    </div>
{/if}
{as4_endForm id="criteriaGroupOptions_{$params.obj->id}"}