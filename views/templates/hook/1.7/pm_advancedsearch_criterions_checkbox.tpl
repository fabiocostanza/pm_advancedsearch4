{***********
Checkbox
************}

{if $criterions_group.display_type eq 4}
	{if isset($as_search.criterions[$criterions_group.id_criterion_group]) && (!$as_search.step_search || isset($as_search.selected_criterions[$criterions_group.id_criterion_group].is_selected))}
		<div class="PM_ASCriterionStepEnable">
		{if sizeof($as_search.criterions[$criterions_group.id_criterion_group])}
			<ul id="PM_ASCriterionGroupCheckbox_{$as_search.id_search|intval}_{$criterions_group.id_criterion_group|intval}" class="PM_ASCriterionGroupCheckbox">
			{if !$criterions_group.is_multicriteria && !isset($criterions_group.is_preselected_by_emplacement)}
				<li>
					<div class="radio">
						<input type="radio" value="" id="as4c_{$criterions_group.id_criterion_group|intval}_0" name="as4c[{$criterions_group.id_criterion_group|intval}][]" {if !isset($as_search.selected_criterion[$criterions_group.id_criterion_group])}checked="checked"{/if} class="PM_ASCriterionCheckbox" /> <label for="as4c_{$criterions_group.id_criterion_group|intval}_0" class="PM_ASLabelCheckbox">{l s='All' mod='pm_advancedsearch4'}</label>
					</div>
				</li>
			{/if}
			{assign var='as_visible_criterions_count' value=0}
			{if isset($as_search.selected_criterion[$criterions_group.id_criterion_group])}
				{assign var='as_visible_criterions_count' value=sizeof($as_search.selected_criterion[$criterions_group.id_criterion_group])}
			{/if}
			{foreach from=$as_search.criterions[$criterions_group.id_criterion_group] key=criterion_key item=criterion name=criterions}
				{if isset($as_search.selected_criterion[$criterions_group.id_criterion_group]) && $criterion.id_criterion|in_array:$as_search.selected_criterion[$criterions_group.id_criterion_group]}
						{assign var='as_criterion_is_selected' value=true}
					{else}
						{assign var='as_criterion_is_selected' value=false}
				{/if}
				{if $criterion_can_hide && $criterions_group.max_display > 0}
					{if $as_criterion_is_selected}
						{assign var='hide_next_criterion' value=false}
					{else}
						{if $as_visible_criterions_count >= $criterions_group.max_display}
							{assign var='hide_next_criterion' value=true}
						{else}
							{assign var='hide_next_criterion' value=false}
							{assign var='as_visible_criterions_count' value=($as_visible_criterions_count + 1)}
						{/if}
					{/if}
				{/if}

				<li{if $hide_next_criterion || !$criterion.nb_product} data-id-criterion-group="{$criterions_group.id_criterion_group|intval}" class="{if $hide_next_criterion}PM_ASCriterionHide{/if}{if !$criterion.nb_product}{if $hide_next_criterion} {/if}PM_ASCriterionDisable{/if}"{/if}>
					{if $criterions_group.is_multicriteria}<div class="checkbox">{else}<div class="radio">{/if}
					<input type="{if $criterions_group.is_multicriteria}checkbox{else}radio{/if}" value="{$criterion.id_criterion nofilter}" data-id-criterion-group="{$criterions_group.id_criterion_group|intval}" id="as4c_{$criterions_group.id_criterion_group|intval}_{$criterion_key|intval}" name="as4c[{$criterions_group.id_criterion_group|intval}][]" {if $as_criterion_is_selected}checked="checked"{/if} class="PM_ASCriterionCheckbox" {if !$criterion.nb_product || !$as_search.selected_criterions[$criterions_group.id_criterion_group].is_selected}disabled="disabled"{/if} /> 
					<label for="as4c_{$criterions_group.id_criterion_group|intval}_{$criterion_key|intval}" class="PM_ASLabelCheckbox{if $as_criterion_is_selected} PM_ASLabelCheckboxSelected{/if}{if !$criterions_group.is_multicriteria} PM_ASNotMulticriteria{/if}">
						<a class="PM_ASLabelLink" href="{if isset($criterion.id_seo) && $criterion.id_seo != false && isset($criterion.seo_page_url) && $criterion.seo_page_url != false}{$criterion.seo_page_url nofilter}{else}#{/if}">
							{$criterion.value}{if $as_search.display_nb_result_criterion} <div class="PM_ASCriterionNbProduct">({$criterion.nb_product})</div>{/if}
						</a>
					</label>
					{if $criterions_group.is_multicriteria}</div>{else}</div>{/if}
				</li>
			{/foreach}
			</ul>
			{if $criterion_can_hide && $criterions_group.max_display > 0 && $as_visible_criterions_count >= $criterions_group.max_display && sizeof($as_search.criterions[$criterions_group.id_criterion_group]) != $as_visible_criterions_count}
				<p data-id-criterion-group="{$criterions_group.id_criterion_group|intval}" class="PM_ASCriterionHideToggle{if $as_search.show_hide_crit_method == 2}Click{/if}{if $as_search.show_hide_crit_method == 1}Hover{/if} PM_ASCriterionHideToggle_{$as_search.id_search|intval}">
					<a href="#" class="PM_ASCriterionHideToggleLink">
						<span class="PM_ASShow">{l s='Show all' mod='pm_advancedsearch4'}</span>
						{if $as_search.show_hide_crit_method == 2}
							<span class="PM_ASHide">{l s='Hide' mod='pm_advancedsearch4'}</span>
						{/if}
					</a>
				</p>
			{/if}
		{else}
			<p class="PM_ASCriterionNoChoice">{l s='No choice available on this group' mod='pm_advancedsearch4'}</p>
		{/if}
		</div>
	{/if}
	{if $as_search.step_search}
		<div data-id-criterion-group="{$criterions_group.id_criterion_group|intval}" class="PM_ASCriterionStepDisable" {if isset($as_search.criterions[$criterions_group.id_criterion_group])} style="display:none;"{/if}>
			<p>{l s='Select above criteria' mod='pm_advancedsearch4'}</p>
		</div>
	{/if}
{/if}