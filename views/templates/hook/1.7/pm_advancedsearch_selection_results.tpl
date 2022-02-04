	<form action="{$ASSearchUrlForm}" method="GET" class="PM_ASSelections PM_ASSelectionsResults">
	{if (is_array($as_searchs[0].selected_criterion) AND sizeof($as_searchs[0].selected_criterion))}
		<p><strong>{l s='Your selection' mod='pm_advancedsearch4'}</strong></p>
		<ul class="PM_ASSelectionsCriterionsGroup">
			{if is_array($as_searchs[0].selected_criterion) AND sizeof($as_searchs[0].selected_criterion) && isset($as_searchs[0].criterions_groups_selected) && is_array($as_searchs[0].criterions_groups_selected)}
				{foreach from=$as_searchs[0].criterions_groups_selected item=criterions_group name=criterions_groups}
					{if $criterions_group.visible}
						{assign var='crit_name_is_display' value=false}
						{foreach from=$as_searchs[0].criterions_selected[$criterions_group.id_criterion_group] key=criterion_key item=criterion name=criterions}
							{if !empty($criterion.visible) && isset($criterion.id_criterion) AND $criterions_group.visible AND $criterion.id_criterion|in_array:$as_searchs[0].selected_criterion[$criterions_group.id_criterion_group]}
								{if !$crit_name_is_display}
									{assign var='crit_name_is_display' value=true}
									<li class="PM_ASSelectionsCriterionsGroupName">
									<strong>{$criterions_group.name} :</strong>
									<ul>
								{/if}
								<li class="PM_ASSelectionsSelectedCriterion">
									<a href="#" class="PM_ASSelectionsRemoveLink">
										{$criterion.value}
									</a>
									<input type="hidden" name="as4c[{$criterions_group.id_criterion_group|intval}][]" value="{$criterion.id_criterion nofilter}" />
								</li>
							{/if}
						{/foreach}
						{if $crit_name_is_display}
							</ul>
						{/if}
						</li>
					{/if}
				{/foreach}
			{/if}
		</ul>
		{foreach from=$as_searchs[0].criterions_groups item=criterions_group name=criterions_groups}
			{if isset($as_searchs[0].selected_criterion[$criterions_group.id_criterion_group])}
				{foreach from=$as_searchs[0].selected_criterion[$criterions_group.id_criterion_group] item=selected_id_criterion name=selected_criteria}
					{if !$criterions_group.visible}
						<input type="hidden" name="as4c[{$criterions_group.id_criterion_group|intval}][]" value="{$selected_id_criterion nofilter}" />
						<input type="hidden" name="as4c_hidden[{$criterions_group.id_criterion_group|intval}][]" value="{$selected_id_criterion nofilter}" />
					{/if}
				{/foreach}
			{/if}
		{/foreach}
		<input type="hidden" name="id_search" value="{$as_searchs[0].id_search|intval}" />
		{if As4SearchEngine::getCurrentCategory()}
			<input type="hidden" name="id_category_search" value="{if isset($as_searchs[0].id_category_root) && $as_searchs[0].id_category_root > 0}{$as_searchs[0].id_category_root|intval}{else if As4SearchEngine::getCurrentCategory()}{As4SearchEngine::getCurrentCategory()|intval}{/if}" />
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
	</form>
