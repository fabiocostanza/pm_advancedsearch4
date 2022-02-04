{if $as_search.show_hide_crit_method eq 1 || $as_search.show_hide_crit_method eq 2}
	{assign var='criterion_can_hide' value=true}
{else}
	{assign var='criterion_can_hide' value=false}
{/if}
{assign var='hide_next_criterion' value=false}
<div id="PM_ASCriterionsOutput_{$as_search.id_search|intval}_{$criterions_group.id_criterion_group|intval}" class="PM_ASCriterionsOutput">
<div id="PM_ASCriterions_{$as_search.id_search|intval}_{$criterions_group.id_criterion_group|intval}" class="PM_ASCriterions{if $criterion_can_hide} PM_ASCriterionsToggle{if $as_search.show_hide_crit_method == 2}Click{/if}{if $as_search.show_hide_crit_method == 1}Hover{/if}{/if}">
{if $as_search.hide_empty_crit_group && $as_search.step_search && (!isset($as_search.criterions[$criterions_group.id_criterion_group]) || !sizeof($as_search.criterions[$criterions_group.id_criterion_group]))}
{else}
<p class="PM_ASCriterionsGroupTitle h4" id="PM_ASCriterionsGroupTitle_{$as_search.id_search|intval}_{$criterions_group.id_criterion_group|intval}" rel="{$criterions_group.id_criterion_group|intval}">
	{if $criterions_group.icon}
		<img src="{$as_path|as4_nofilter}search_files/criterions_group/{$criterions_group.icon|escape:'htmlall':'UTF-8'}" alt="{$criterions_group.name|escape:'htmlall':'UTF-8'}" title="{$criterions_group.name|escape:'htmlall':'UTF-8'}" id="PM_ASCriterionsGroupIcon_{$as_search.id_search|intval}_{$criterions_group.id_criterion_group|intval}" class="PM_ASCriterionsGroupIcon" />
	{/if}
	<span class="PM_ASCriterionsGroupName">
		{$criterions_group.name|escape:'htmlall':'UTF-8'}
	</span>
</p>

<div class="PM_ASCriterionsGroupOuter">

{assign var='tpl_name' value='pm_advancedsearch_criterions_'|cat:$as_criteria_group_type_interal_name[$criterions_group.display_type]|cat:'.tpl'}
{include file=$as_obj->_getTplPath($tpl_name)}
</div>
{if $as_search.reset_group|intval && isset($as_search.selected_criterion[$criterions_group.id_criterion_group]) && sizeof($as_search.selected_criterion[$criterions_group.id_criterion_group])}
	<div class="clear"></div>
	<a href="#" class="PM_ASResetGroup" rel="{$criterions_group.id_criterion_group|intval}">
		{l s='Reset this group' mod='pm_advancedsearch4'}
	</a>
{/if}
{/if}
{if $as_search.step_search && !isset($as_search.selected_criterion[$criterions_group.id_criterion_group]) && empty($criterions_group.is_skipped) && !empty($as_search.criterions[$criterions_group.id_criterion_group]) && empty($criterions_group.next_group_have_selected_values)}
	<a href="#" class="PM_ASSkipGroup" rel="{$criterions_group.id_criterion_group|intval}">
		{l s='Skip this step' mod='pm_advancedsearch4'}
	</a>
{/if}
</div>
<div class="clear"></div>
</div>
{if $as_search.step_search}
	<input type="hidden" name="current_id_criterion_group" value="{$criterions_group.id_criterion_group|intval}" disabled="disabled" />
	{if !empty($criterions_group.is_skipped)}
		<input type="hidden" name="as4c[{$criterions_group.id_criterion_group|intval}][]" value="-1" />
	{else}
		<input type="hidden" name="as4c[{$criterions_group.id_criterion_group|intval}][]" value="-1" disabled="disabled" />
	{/if}
{/if}

{if isset($next_id_criterion_group)}
<script type="text/javascript">
	{if is_array($as_search.selected_criterion) && sizeof($as_search.selected_criterion) && $as_search.selected_criterion != $as_search.selected_criterion_from_emplacement && !empty($ajaxMode)}
	if(typeof(as4Plugin.locationName) != 'undefined' && as4Plugin.locationName) $('#PM_ASBlock_{$as_search.id_search|intval} .PM_ASResetSearch').html("{l s='Back to' mod='pm_advancedsearch4' js=1} " + as4Plugin.locationName);
		$('#PM_ASBlock_{$as_search.id_search|intval} .PM_ASResetSearch').css('display','block');
	{/if}
	//Update nb product display
	$('#PM_ASBlock_{$as_search.id_search|intval} .PM_ASBlockNbProductValue').html("({$as_search.total_products|intval} {if $as_search.total_products > 1}{l s='products' mod='pm_advancedsearch4'}{else}{l s='product' mod='pm_advancedsearch4'}{/if})");
	{if $next_id_criterion_group && (!isset($as_search.criterions[$criterions_group.id_criterion_group]) || ! sizeof($as_search.criterions[$criterions_group.id_criterion_group]))}

		as4Plugin.nextStep({$as_search.id_search|intval},$('#PM_ASCriterions_{$as_search.id_search|intval}_{$criterions_group.id_criterion_group|intval}'),true,{$as_search.search_method|intval});
	{/if}
	{if $as_search.search_method == 2}
		$('#PM_ASForm_{$as_search.id_search|intval}').ajaxForm(as4Plugin.getASFormOptions({$as_search.id_search|intval}));
	{/if}
	as4Plugin.initSearchBlock({$as_search.id_search|intval},{$as_search.search_method|intval},{$as_search.step_search|intval});
</script>
{/if}