{***********
Slider
************}
{if $criterions_group.display_type eq 5}
	{if isset($as_search.criterions[$criterions_group.id_criterion_group]) && (!$as_search.step_search || isset($as_search.selected_criterions[$criterions_group.id_criterion_group].is_selected))}
		<div class="PM_ASCriterionStepEnable">
		{if sizeof($as_search.criterions[$criterions_group.id_criterion_group])}
			{foreach from=$as_search.criterions[$criterions_group.id_criterion_group] item=criterion name=criterions}
				{if (isset($criterion.cur_min) && isset($criterion.cur_max) && $criterion.cur_min == 0 && $criterion.cur_max == 0) || (isset($criterion.min) && isset($criterion.max) && $criterion.min == 0 && $criterion.max == 0)}
					<p class="PM_ASCriterionNoChoice">{l s='No choice available on this group' mod='pm_advancedsearch4'}</p>
					<input type="hidden" name="as4c[{$criterions_group.id_criterion_group|intval}][]" id="PM_ASInputCritRange{$as_search.id_search|intval}_{$criterions_group.id_criterion_group|intval}" value="{if isset($criterion.cur_min)}{$criterion.cur_min|floatval}-{$criterion.cur_max|floatval}{/if}" />
				{else}
					<div
						class="PM_ASCritRange"
						id="PM_ASCritRange{$as_search.id_search|intval}_{$criterions_group.id_criterion_group|intval}"
						data-id-search="{$as_search.id_search|intval}"
						data-id-criterion-group="{$criterions_group.id_criterion_group|intval}"
						data-min="{$criterion.min|floatval}"
						data-max="{$criterion.max|floatval}"
						data-step="{$criterion.step|floatval}"
						data-values="[ {if isset($criterion.cur_min)}{$criterion.cur_min|floatval}, {$criterion.cur_max|floatval}{else}{$criterion.min|floatval}, {$criterion.max|floatval}{/if} ]"
						data-disabled="{if isset($as_search.selected_criterions[$criterions_group.id_criterion_group].is_selected) && !$as_search.selected_criterions[$criterions_group.id_criterion_group].is_selected}true{else}false{/if}"
						data-left-range-sign="{if isset($criterions_group.left_range_sign)}{$criterions_group.left_range_sign|escape:'htmlall':'UTF-8'}{/if}"
						data-right-range-sign="{if isset($criterions_group.right_range_sign)}{$criterions_group.right_range_sign|escape:'htmlall':'UTF-8'}{/if}"
					></div>
					<span class="PM_ASCritRangeValue" id="PM_ASCritRangeValue{$as_search.id_search|intval}_{$criterions_group.id_criterion_group|intval}">
						{if isset($criterion.cur_min)}
							{if isset($criterions_group.left_range_sign)}{$criterions_group.left_range_sign|escape:'htmlall':'UTF-8'}{/if}{$criterion.cur_min|floatval}{if isset($criterions_group.right_range_sign)}{$criterions_group.right_range_sign|escape:'htmlall':'UTF-8'}{/if} - {if isset($criterions_group.left_range_sign)}{$criterions_group.left_range_sign|escape:'htmlall':'UTF-8'}{/if}{$criterion.cur_max|floatval}{if isset($criterions_group.right_range_sign)}{$criterions_group.right_range_sign|escape:'htmlall':'UTF-8'}{/if}
						{else}
							{if isset($criterions_group.left_range_sign)}{$criterions_group.left_range_sign|escape:'htmlall':'UTF-8'}{/if}{$criterion.min|floatval}{if isset($criterions_group.right_range_sign)}{$criterions_group.right_range_sign|escape:'htmlall':'UTF-8'}{/if} - {if isset($criterions_group.left_range_sign)}{$criterions_group.left_range_sign|escape:'htmlall':'UTF-8'}{/if}{$criterion.max|floatval}{if isset($criterions_group.right_range_sign)}{$criterions_group.right_range_sign|escape:'htmlall':'UTF-8'}{/if}
						{/if}
					</span>
					<input type="hidden" name="as4c[{$criterions_group.id_criterion_group|intval}][]" id="PM_ASInputCritRange{$as_search.id_search|intval}_{$criterions_group.id_criterion_group|intval}" value="{if isset($criterion.cur_min)}{$criterion.cur_min|floatval}~{$criterion.cur_max|floatval}{/if}" data-id-criterion-group="{$criterions_group.id_criterion_group|intval}" />
					<script type="text/javascript">
					if (typeof(as4Plugin) != 'undefined') {
						as4Plugin.initSliders();
					}
					</script>
				{/if}
			{/foreach}
		{else}
			<p class="PM_ASCriterionNoChoice">{l s='No choice available on this group' mod='pm_advancedsearch4'}</p>
		{/if}
		</div>
	{/if}
	{if $as_search.step_search}
		<div class="PM_ASCriterionStepDisable" {if isset($as_search.criterions[$criterions_group.id_criterion_group])} style="display:none;"{/if}>
			<div data-id-criterion-group="{$criterions_group.id_criterion_group|intval}" class="PM_ASCriterionStepDisable" {if isset($as_search.criterions[$criterions_group.id_criterion_group])} style="display:none;"{/if}>
				<p>{l s='Select above criteria' mod='pm_advancedsearch4'}</p>
			</div>
		</div>
	{/if}
{/if}