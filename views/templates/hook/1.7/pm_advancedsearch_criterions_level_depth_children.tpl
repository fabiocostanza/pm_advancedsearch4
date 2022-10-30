{*********************
Level Depth - Children
**********************}
{if isset($as_search.selected_criterions_ld[$criterions_group.id_criterion_group]) && $criterion.id_criterion_linked|in_array:$as_search.selected_criterions_ld[$criterions_group.id_criterion_group]}
	{assign var='as_criterion_is_selected' value=true}
{else}
	{assign var='as_criterion_is_selected' value=false}
{/if}

{if isset($as_search.selected_criterions_ld[$criterions_group.id_criterion_group][0]) && $criterion.id_criterion_linked == $as_search.selected_criterions_ld[$criterions_group.id_criterion_group][0]}
	{assign var='as_criterion_selected' value=$criterion.id_criterion}
{else}
	{assign var='as_criterion_selected' value=false}
{/if}

{if isset($in_select) && $in_select}
	<option value="{$criterion.id_criterion|intval}" {if isset($as_criterion_is_selected) && $as_criterion_is_selected}selected="selected"{/if}{if !$criterion.nb_product || !$as_search.selected_criterions[$criterions_group.id_criterion_group].is_selected} class="PM_ASCriterionDisable" disabled="disabled"{/if}>{$criterion.value}</option>
{else}
	{assign var='in_select' value=0}
	<li class="{if isset($as_criterion_is_selected) && $as_criterion_is_selected}PM_ASCriterionLevelSelected {/if}{if isset($as_criterion_selected) && $as_criterion_selected}PM_ASCriterionLevelChoosen {/if}PM_ASCriterionLevel level{$level_depth|intval}"
		data-id-category="{$criterion.id_criterion|intval}"
		data-id-parent="{$criterion.id_parent|intval}"
		data-level-depth="{$criterion.level_depth|intval}"
		{if $level_depth eq 1 || isset($as_criterion_is_selected) && $as_criterion_is_selected}

		{else}
			{if isset($as_search.selected_criterions_ld[$criterions_group.id_criterion_group]) && $criterion.id_parent|in_array:$as_search.selected_criterions_ld[$criterions_group.id_criterion_group]}

			{else}
				style="display: none;"
			{/if}
		{/if}>
		{if isset($as_search.criterions_childrens[$criterions_group.id_criterion_group][$criterion.id_criterion_linked]) && count($as_search.criterions_childrens[$criterions_group.id_criterion_group][$criterion.id_criterion_linked]) > 0}
			<span class="grower PM_ASCriterionOpenClose
				{if isset($as_criterion_is_selected) && $as_criterion_is_selected}
					PM_ASCriterionOpen
				{else}
					{if isset($as_search.selected_criterions_ld[$criterions_group.id_criterion_group]) && $criterion.id_criterion_linked|in_array:$as_search.selected_criterions_ld[$criterions_group.id_criterion_group]}
						PM_ASCriterionOpen
					{else}
						PM_ASCriterionClose
					{/if}
				{/if}" data-id-category="{$criterion.id_criterion_linked|intval}"><i class="material-icons add"></i><i class="material-icons remove"></i></span>
		{/if}
		<a href="#" class="PM_ASCriterionLevelChoose" data-id-criterion-group="{$criterions_group.id_criterion_group|intval}" data-id-criterion="{$criterion.id_criterion|intval}">
			{$criterion.value}{if $as_search.display_nb_result_criterion} <div class="PM_ASCriterionNbProduct">({$criterion.nb_product})</div>{/if}
		</a>
	</li>
{/if}

{if isset($as_search.criterions_childrens[$criterions_group.id_criterion_group][$criterion.id_criterion_linked])}
	{assign var='level_depth' value=$level_depth+1}
	{foreach from=$as_search.criterions_childrens[$criterions_group.id_criterion_group][$criterion.id_criterion_linked] item=children name=childrens key=c}
		{include file=$as_obj->getTplPath("pm_advancedsearch_criterions_level_depth_children.tpl") level_depth=$level_depth criterion=$children in_select=$in_select}
	{/foreach}
{/if}
