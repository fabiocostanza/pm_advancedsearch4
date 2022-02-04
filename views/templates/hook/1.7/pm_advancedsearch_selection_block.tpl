{if empty($selectionFromGroups)}<section id="js-active-search-filters">{/if}
	{if (is_array($as_search.selected_criterion) && sizeof($as_search.selected_criterion))}
	<form action="{$ASSearchUrlForm}" method="GET" class="PM_ASSelectionsBlock PM_ASSelections active_filters" data-id-search="{$as_search.id_search|intval}">
		{if is_array($as_search.selected_criterion) && sizeof($as_search.selected_criterion)}
			{assign var='current_selection' value=$as_search.selected_criterion}
		{else}
			{assign var='current_selection' value=array()}
		{/if}

		{assign var='hasOneVisibleSelectedCriterion' value=false}
		{capture assign="selectedCriterionsHtml"}
		<ul>
			{foreach from=$as_search.criterions_groups_selected item=criterions_group name=criterions_groups}
				{if isset($as_search.criterions[$criterions_group.id_criterion_group]) && $criterions_group.visible && isset($current_selection[$criterions_group.id_criterion_group]) && is_array($current_selection[$criterions_group.id_criterion_group]) && sizeof($current_selection[$criterions_group.id_criterion_group])}
					{foreach from=$as_search.criterions_selected[$criterions_group.id_criterion_group] key=criterion_key item=criterion name=criterions}
						{if !empty($criterion.visible) && (isset($criterion.id_criterion) AND isset($as_search.selected_criterion[$criterions_group.id_criterion_group]) AND is_array($as_search.selected_criterion[$criterions_group.id_criterion_group]) AND $criterion.id_criterion|in_array:$as_search.selected_criterion[$criterions_group.id_criterion_group]) || isset($criterion.min)}
							{assign var='hasOneVisibleSelectedCriterion' value=true}
							<li>
								<a href="#" class="PM_ASSelectionsRemoveLink filter-block">{$criterions_group.name} : {if isset($criterion.min) && isset($criterion.max)}{$criterion.min|floatval} {$criterion.max|floatval}{else}{$criterion.value}{/if}</a>
								<input type="hidden" name="as4c[{$criterions_group.id_criterion_group|intval}][]" value="{$criterion.id_criterion nofilter}" />
							</li>
						{/if}
					{/foreach}
					</li>
				{/if}
			{/foreach}
		</ul>
		{/capture}

		{if !empty($selectedCriterionsHtml) && !empty($hasOneVisibleSelectedCriterion)}
			<div class="h6 active-filter-title">{l s='Active filters' mod='pm_advancedsearch4'}</div>
			{$selectedCriterionsHtml nofilter}
		{/if}

		{* Hidden criterions *}
		{foreach from=$as_search.criterions_groups item=criterions_group name=criterions_groups}
			{if isset($as_search.selected_criterion[$criterions_group.id_criterion_group])}
				{foreach from=$as_search.selected_criterion[$criterions_group.id_criterion_group] item=selected_id_criterion name=selected_criteria}
					{if !$criterions_group.visible}
						<input type="hidden" name="as4c[{$criterions_group.id_criterion_group|intval}][]" value="{$selected_id_criterion nofilter}" />
						<input type="hidden" name="as4c_hidden[{$criterions_group.id_criterion_group|intval}][]" value="{$selected_id_criterion nofilter}" />
					{/if}
				{/foreach}
			{/if}
		{/foreach}
		<input type="hidden" name="id_search" value="{$as_search.id_search|intval}" />
		{if As4SearchEngine::getCurrentCategory()}
			<input type="hidden" name="id_category_search" value="{if isset($as_search.id_category_root) && $as_search.id_category_root > 0}{$as_search.id_category_root|intval}{else if As4SearchEngine::getCurrentCategory()}{As4SearchEngine::getCurrentCategory()|intval}{/if}" />
		{/if}
		{if As4SearchEngine::getCurrentManufacturer()}
			<input type="hidden" name="id_manufacturer_search" value="{As4SearchEngine::getCurrentManufacturer()|intval}" />
		{/if}
		{if As4SearchEngine::getCurrentSupplier()}
			<input type="hidden" name="id_supplier_search" value="{As4SearchEngine::getCurrentSupplier()|intval}" />
		{/if}
		{if isset($smarty.get.id_seo)}
			<input type="hidden" name="id_seo" value="{$smarty.get.id_seo|intval}" />
		{/if}

	</form>
	{/if}
{if empty($selectionFromGroups)}</section><!-- #js-active-search-filters -->{/if}