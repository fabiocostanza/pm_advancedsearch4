{***********
Level Depth
************}
{if $criterions_group.display_type eq 9}
	{if isset($as_search.criterions[$criterions_group.id_criterion_group]) && (!$as_search.step_search || isset($as_search.selected_criterions[$criterions_group.id_criterion_group].is_selected))}
		<div class="PM_ASCriterionStepEnable">
		{if sizeof($as_search.criterions[$criterions_group.id_criterion_group])}
			<select data-id-criterion-group="{$criterions_group.id_criterion_group|intval}" name="as4c[{$criterions_group.id_criterion_group|intval}][]" id="PM_ASCriterionGroupSelect_{$as_search.id_search|intval}_{$criterions_group.id_criterion_group|intval}" class="PM_ASCriterionGroupSelect" style="display: none;">
			<option value="">{if isset($criterions_group.all_label) && $criterions_group.all_label != ''}{$criterions_group.all_label|escape:'htmlall':'UTF-8'}{else}{l s='All' mod='pm_advancedsearch4'}{/if}</option>
			{foreach from=$as_search.criterions[$criterions_group.id_criterion_group] item=criterion name=criterions key=i}
				{include file=$as_obj->_getTplPath("pm_advancedsearch_criterions_level_depth_children.tpl") level_depth=1 criterion=$criterion in_select=1}
			{/foreach}
			</select>

			{assign var='as_criterion_is_selected' value=false}
			{assign var='as_criterion_selected' value=false}

			<ul class="PM_ASLevelDepth" data-id-criterion-group="{$criterions_group.id_criterion_group|intval}">
			{foreach from=$as_search.criterions[$criterions_group.id_criterion_group] item=criterion name=criterions key=i}
				{include file=$as_obj->_getTplPath("pm_advancedsearch_criterions_level_depth_children.tpl") level_depth=1 criterion=$criterion}
			{/foreach}
			</ul>
		{else}
			<p class="PM_ASCriterionNoChoice">{l s='No choice available on this group' mod='pm_advancedsearch4'}</p>
		{/if}
		</div>
	{/if}
	{if $as_search.step_search}
		<div class="PM_ASCriterionStepDisable" {if isset($as_search.criterions[$criterions_group.id_criterion_group])} style="display:none;"{/if}>
			<select data-id-criterion-group="{$criterions_group.id_criterion_group|intval}" disabled="disabled" name="as4c[{$criterions_group.id_criterion_group|intval}][]" id="PM_ASCriterionGroupSelect_{$as_search.id_search|intval}_{$criterions_group.id_criterion_group|intval}" class="PM_ASCriterionGroupSelect {if isset($criterions_group.filter_option) && $criterions_group.filter_option == 1}as4-select{else}form-control{/if}">
				<option value="">{l s='Select above criteria' mod='pm_advancedsearch4'}</option>
			</select>
		</div>
	{/if}
{/if}