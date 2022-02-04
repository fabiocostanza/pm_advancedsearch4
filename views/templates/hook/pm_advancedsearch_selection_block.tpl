	<form action="{$ASSearchUrlForm|escape:'htmlall':'UTF-8'}" method="GET" class="PM_ASSelectionsBlock PM_ASSelections" data-id-search="{$as_search.id_search|intval}">
	<div class="PM_ASSelectionsInner">
	{if (is_array($as_search.selected_criterion) && sizeof($as_search.selected_criterion))}
		{if is_array($as_search.selected_criterion) && sizeof($as_search.selected_criterion)}
			{assign var='current_selection' value=$as_search.selected_criterion}
		{else}
			{assign var='current_selection' value=array()}
		{/if}
		{if $hookName eq 'leftcolumn' || $hookName eq 'rightcolumn'}
		<div class="PM_ASSelectionsDropDown" id="PM_ASSelectionsDropDown_{$as_search.id_search|intval}">
		<a href="#" class="PM_ASSelectionsDropDownShowLink">
			<strong>{l s='Your selection' mod='pm_advancedsearch4'}</strong>
		</a>
		<div class="PM_ASSelectionsDropDownMenu">
		{/if}
		<ul class="PM_ASSelectionsCriterionsGroup">
			{foreach from=$as_search.criterions_groups_selected item=criterions_group name=criterions_groups}
				{if isset($as_search.criterions[$criterions_group.id_criterion_group]) && $criterions_group.visible && isset($current_selection[$criterions_group.id_criterion_group]) && is_array($current_selection[$criterions_group.id_criterion_group]) && sizeof($current_selection[$criterions_group.id_criterion_group])}
					{assign var='crit_name_is_display' value=false}
					{foreach from=$as_search.criterions_selected[$criterions_group.id_criterion_group] key=criterion_key item=criterion name=criterions}
						{if !empty($criterion.visible) && (isset($criterion.id_criterion) AND isset($as_search.selected_criterion[$criterions_group.id_criterion_group]) AND is_array($as_search.selected_criterion[$criterions_group.id_criterion_group]) AND $criterion.id_criterion|in_array:$as_search.selected_criterion[$criterions_group.id_criterion_group]) || isset($criterion.min)}
							{if !$crit_name_is_display}
								{assign var='crit_name_is_display' value=true}
								<li class="PM_ASSelectionsCriterionsGroupName">
								<strong>{$criterions_group.name|escape:'htmlall':'UTF-8'} :</strong>
								<ul>
							{/if}
							<li class="PM_ASSelectionsSelectedCriterion">
								<a href="#" class="PM_ASSelectionsRemoveLink">
									{if isset($criterion.min) && isset($criterion.max)}{$criterion.min|floatval} {$criterion.max|floatval}{else}{$criterion.value|escape:'htmlall':'UTF-8'}{/if}
								</a>
								<input type="hidden" name="as4c[{$criterions_group.id_criterion_group|intval}][]" value="{$criterion.id_criterion|as4_nofilter}" />
							</li>
						{/if}
					{/foreach}
					{if $crit_name_is_display}
						</ul>
					{/if}
					</li>
				{/if}
			{/foreach}
		</ul>
		{* Hidden criterions *}
		{foreach from=$as_search.criterions_groups item=criterions_group name=criterions_groups}
			{if isset($as_search.selected_criterion[$criterions_group.id_criterion_group])}
				{foreach from=$as_search.selected_criterion[$criterions_group.id_criterion_group] item=selected_id_criterion name=selected_criteria}
					{if !$criterions_group.visible}
						<input type="hidden" name="as4c[{$criterions_group.id_criterion_group|intval}][]" value="{$selected_id_criterion|as4_nofilter}" />
						<input type="hidden" name="as4c_hidden[{$criterions_group.id_criterion_group|intval}][]" value="{$selected_id_criterion|as4_nofilter}" />
					{/if}
				{/foreach}
			{/if}
		{/foreach}
		{if $hookName eq 'leftcolumn' || $hookName eq 'rightcolumn'}
		</div>
		</div>
		<script type="text/javascript">if(!$('#PM_ASSelectionsDropDown_{$as_search.id_search|intval} div ul li').length)$('#PM_ASSelectionsDropDown_{$as_search.id_search|intval}').hide();</script>
		{/if}
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
	{/if}

	</div>
	</form>