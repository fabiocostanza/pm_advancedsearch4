{***********
Filter color link
************}

{if $criterions_group.display_type eq 7}
	{if isset($as_search.criterions[$criterions_group.id_criterion_group])}
		<div class="{if $as_search.selected_criterions[$criterions_group.id_criterion_group].is_selected}PM_ASCriterionStepEnable{else}PM_ASCriterionDisable{/if}">
		{if sizeof($as_search.criterions[$criterions_group.id_criterion_group])}
			<ul id="PM_ASCriterionGroupColor_{$as_search.id_search|intval}_{$criterions_group.id_criterion_group|intval}" class="PM_ASCriterionGroupColor color_to_pick_list">
			{assign var='as_visible_criterions_count' value=0}
			{if isset($as_search.selected_criterion[$criterions_group.id_criterion_group])}
				{assign var='as_visible_criterions_count' value=sizeof($as_search.selected_criterion[$criterions_group.id_criterion_group])}
			{/if}
			{foreach from=$as_search.criterions[$criterions_group.id_criterion_group] item=criterion key=criterion_key name=criterions}
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
				<li{if $hide_next_criterion || !$criterion.nb_product} class="{if $hide_next_criterion}PM_ASCriterionHide{/if}{if !$criterion.nb_product}{if $hide_next_criterion} {/if}PM_ASCriterionDisable{/if}"{/if}>
					<a href="{if isset($criterion.id_seo) && $criterion.id_seo != false && isset($criterion.seo_page_url) && $criterion.seo_page_url != false}{$criterion.seo_page_url|escape:'htmlall':'UTF-8'}{else}#{/if}" data-id-criterion-group="{$criterions_group.id_criterion_group|intval}" class="PM_ASCriterionLink {if $as_criterion_is_selected}PM_ASCriterionLinkSelected{/if}{if !$criterions_group.is_multicriteria} PM_ASNotMulticriteria{/if}" title="{$criterion.value|escape:'htmlall':'UTF-8'}{if $as_search.display_nb_result_criterion} ({$criterion.nb_product|escape:'htmlall':'UTF-8'}){/if}" style="background:{$criterion.color|escape:'htmlall':'UTF-8'}{if empty($criterion.is_custom) && Tools::file_exists_cache($col_img_dir|cat:$criterion.id_criterion_linked|cat:'.jpg')} url({$img_col_dir|escape:'htmlall':'UTF-8'}{$criterion.id_criterion_linked|intval}.jpg); background-size: cover{/if};"></a>
					<input type="hidden" name="as4c[{$criterions_group.id_criterion_group|intval}][]" value="{$criterion.id_criterion|intval}" {if !$as_criterion_is_selected}disabled="disabled"{/if} />
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