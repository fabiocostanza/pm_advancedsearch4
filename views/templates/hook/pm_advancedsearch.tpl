{if isset($as_searchs)}
	{if $hookName == 'top'}<div class="clear"></div>{/if}
	{if isset($hideAS4Form) && $hideAS4Form == true}<div id="PM_ASFormContainerHidden" style="display: none">{/if}
	{foreach from=$as_searchs item=as_search name=as_searchs}
		{assign var='next_id_criterion_group_isset' value=false}
		{include file=$as_obj->_getTplPath("pm_advancedsearch_header_block.tpl")}
		{if $as_search.remind_selection == 3 OR $as_search.remind_selection == 2}
			{include file=$as_obj->_getTplPath("pm_advancedsearch_selection_block.tpl")}
		{/if}
		<a {if !isset($smarty.get.id_seo) && isset($as_selected_criterion) && is_array($as_selected_criterion) && !sizeof($as_selected_criterion)}style="display: none" {/if}href="#" class="PM_ASResetSearch">{l s='Back' mod='pm_advancedsearch4'}</a>
		<form action="{$ASSearchUrlForm|escape:'htmlall':'UTF-8'}" method="GET" id="PM_ASForm_{$as_search.id_search|intval}" class="PM_ASForm">
			<div class="PM_ASCriterionsGroupList{if $hookName != 'leftcolumn' && $hookName != 'rightcolumn'} row{/if}">
		{foreach from=$as_search.criterions_groups item=criterions_group name=criterions_groups}
			{capture name="as4_input_hidden_criterions"}
				{if isset($as_search.selected_criterion[$criterions_group.id_criterion_group])}
					{foreach from=$as_search.selected_criterion[$criterions_group.id_criterion_group] item=selected_id_criterion name=selected_criteria}
						{if !$criterions_group.visible}
							<input type="hidden" name="as4c[{$criterions_group.id_criterion_group|intval}][]" value="{$selected_id_criterion|as4_nofilter}" />
							<input type="hidden" name="as4c_hidden[{$criterions_group.id_criterion_group|intval}][]" value="{$selected_id_criterion|as4_nofilter}" />
						{/if}
					{/foreach}
				{/if}
			{/capture}
			{if !(isset($as_criteria_group_type_interal_name[$criterions_group.display_type]) && ($as_criteria_group_type_interal_name[$criterions_group.display_type] == 'slider' || $as_criteria_group_type_interal_name[$criterions_group.display_type] == 'range') && isset($as_search.criterions[$criterions_group.id_criterion_group]) && isset($as_search.criterions[$criterions_group.id_criterion_group][0]) && ((isset($as_search.criterions[$criterions_group.id_criterion_group][0].cur_min) && isset($as_search.criterions[$criterions_group.id_criterion_group][0].cur_max) && $as_search.criterions[$criterions_group.id_criterion_group][0].cur_min == 0 && $as_search.criterions[$criterions_group.id_criterion_group][0].cur_max == 0) || (isset($as_search.criterions[$criterions_group.id_criterion_group][0].min) && isset($as_search.criterions[$criterions_group.id_criterion_group][0].max) && $as_search.criterions[$criterions_group.id_criterion_group][0].min == 0 && $as_search.criterions[$criterions_group.id_criterion_group][0].max == 0))) && ($criterions_group.visible && $as_search.hide_empty_crit_group && isset($as_search.criterions[$criterions_group.id_criterion_group]) && sizeof($as_search.criterions[$criterions_group.id_criterion_group])) || ($criterions_group.visible && !$as_search.hide_empty_crit_group) || ($criterions_group.visible && $as_search.step_search)}
				{if $criterions_group.hidden eq '1' && !isset($hidden_criteria_group_open)}
					{assign var='hidden_criteria_group_open' value=true}
					<p class="PM_ASShowCriterionsGroupHidden{if isset($as_search.advanced_search_open) && $as_search.advanced_search_open} PM_ASShowCriterionsGroupHiddenOpen{/if}"><a href="#">{l s='Show/hide more options' mod='pm_advancedsearch4'}</a></p>
				{/if}
				<div id="PM_ASCriterionsGroup_{$as_search.id_search|intval}_{$criterions_group.id_criterion_group|intval}" class="{if isset($as_search.seo_criterion_groups) && is_array($as_search.seo_criterion_groups) && in_array($criterions_group.id_criterion_group,$as_search.seo_criterion_groups)}PM_ASCriterionsSEOGroupDisabled {/if}PM_ASCriterionsGroup{if $criterions_group.hidden} PM_ASCriterionsGroupHidden{/if}{if $as_search.hide_empty_crit_group && $as_search.step_search && (!isset($as_search.criterions[$criterions_group.id_criterion_group]) || !sizeof($as_search.criterions[$criterions_group.id_criterion_group]))} PM_ASCriterionsGroupHidden{/if} PM_ASCriterionsGroup{$criterions_group.criterion_group_type|ucfirst|escape:'htmlall':'UTF-8'} {if $hookName != 'leftcolumn' && $hookName != 'rightcolumn'}{$criterions_group.css_classes|escape:'htmlall':'UTF-8'}{/if}"{if isset($as_search.advanced_search_open) && $as_search.advanced_search_open} style="display:block;"{/if}>
					{include file=$as_obj->_getTplPath("pm_advancedsearch_criterions.tpl")}
				</div>
				{if $as_search.step_search && $next_id_criterion_group_isset == false && !isset($as_search.criterions[$criterions_group.id_criterion_group])}
					{assign var='next_id_criterion_group_isset' value=true}
				{/if}
			{/if}
		{/foreach}
			</div><!-- .PM_ASCriterionsGroupList -->
		{$smarty.capture.as4_input_hidden_criterions|as4_nofilter}
		{if $as_search.reset_group|intval}
		<input type="hidden" name="reset_group" value="" />
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
		{if $as_search.step_search}
		<input type="hidden" name="next_id_criterion_group" value="" />
		{/if}
		<input type="hidden" name="orderby"{if isset($smarty.get.orderby) && $smarty.get.orderby} value="{$smarty.get.orderby|escape:'htmlall':'UTF-8'}"{else} disabled="disabled"{/if} />
		<input type="hidden" name="orderway"{if isset($smarty.get.orderway) && $smarty.get.orderway} value="{$smarty.get.orderway|escape:'htmlall':'UTF-8'}"{else} disabled="disabled"{/if} />
		<input type="hidden" name="n"{if isset($smarty.get.n) && $smarty.get.n} value="{$smarty.get.n|intval}"{else} disabled="disabled"{/if} />
		{if $as_search.search_method == 2}
			<input type="submit" value="{l s='Search' mod='pm_advancedsearch4'}" name="submitAsearch" class="btn btn-default PM_ASSubmitSearch" />
		{/if}
		
		{if isset($smarty.get.id_seo)}
		<input type="hidden" name="id_seo" value="{$smarty.get.id_seo|intval}" />
		{/if}

		<script type="text/javascript">
			as4Plugin.params[{$as_search.id_search|intval}] = {ldelim}
				'hookName'						: '{$hookName|escape:'htmlall':'UTF-8'}',
				'centerColumnCssClasses'		: {$as_search.search_results_selector_css|json_encode},
				'availableCriterionsGroups'		: {if isset($as_search.criterionsGroupsMini)}{$as_search.criterionsGroupsMini|json_encode}{else}{ldelim}{rdelim}{/if},
				'selectedCriterions'			: {if isset($as_search.criterions_selected)}{$as_search.criterions_selected|json_encode}{else}{ldelim}{rdelim}{/if},
				'stepSearch'					: {$as_search.step_search|intval},
				'searchMethod' 					: {$as_search.search_method|intval},
				'keep_category_information' 	: {if As4SearchEngine::getCurrentCategory() || As4SearchEngine::getCurrentManufacturer() || As4SearchEngine::getCurrentSupplier() || !empty($smarty.get.seo_url)}{$as_search.keep_category_information|intval}{else}0{/if},
				'search_results_selector'		: '{$as_search.search_results_selector|escape:'htmlall':'UTF-8'}',
				'insert_in_center_column'		: {$as_search.insert_in_center_column|intval},
				'seo_criterion_groups'			: '{if isset($as_search.seo_criterion_groups) && is_array($as_search.seo_criterion_groups)}{","|implode:$as_search.seo_criterion_groups|escape:'htmlall':'UTF-8'}{/if}',
				'as4_productFilterListData'		: {if isset($as4_productFilterListData) && !empty($as4_productFilterListData)}{$as4_productFilterListData|json_encode}{else}''{/if},
				'as4_productFilterListSource'	: {if isset($as4_productFilterListSource) && !empty($as4_productFilterListSource)}{$as4_productFilterListSource|json_encode}{else}''{/if},
				'scrollTopActive'				: {if isset($as_search.scrolltop_active) && $as_search.scrolltop_active}true{else}false{/if},
				'resetURL'						: {if is_array($as_search.selected_criterion) && sizeof($as_search.selected_criterion)}{As4SearchEngine::generateURLFromCriterions($as_search.id_search)|json_encode}{else}''{/if}
			{rdelim};
		{if isset($as_location_name) && $as_location_name}
			as4Plugin.locationName = {$as_location_name|json_encode};
			if(typeof(as4Plugin.locationName) != 'undefined' && as4Plugin.locationName) {ldelim}
				$(document).ready(function() {ldelim}
					$('#PM_ASBlock_{$as_search.id_search|intval} .PM_ASResetSearch').html("{l s='Back to' mod='pm_advancedsearch4' js=1} " + as4Plugin.locationName);
				{rdelim});
			{rdelim}
		{/if}
		{if is_array($as_search.selected_criterion) && sizeof($as_search.selected_criterion) && $as_search.selected_criterion != $as_search.selected_criterion_from_emplacement && isset($ajaxMode) && $ajaxMode}
			$('#PM_ASBlock_{$as_search.id_search|intval} .PM_ASResetSearch').css('display','block');
		{/if}
		{if !empty($as4_localCacheKey)}
			as4Plugin.localCacheKey = {$as4_localCacheKey|json_encode};
		{/if}
		{if isset($as4_localCache)}
			as4Plugin.localCache = {$as4_localCache|json_encode};
		{/if}
		as4Plugin.blurEffect = {$as4_blurEffect|json_encode};
		{if $page_name == 'module-pm_advancedsearch4-searchresults' || !empty($as_is_seo_page)}
			{if !empty($orderby)}
				as4Plugin.params[{$as_search.id_search|intval}].orderBy = {$orderby|escape:'htmlall':'UTF-8'|json_encode};
			{/if}
			{if !empty($orderway)}
				as4Plugin.params[{$as_search.id_search|intval}].orderWay = {$orderway|escape:'htmlall':'UTF-8'|json_encode};
			{/if}
			as4Plugin.params[{$as_search.id_search|intval}].addBestSalesOption = true;
		{/if}
		
			as4Plugin.initSearchBlock({$as_search.id_search|intval},{$as_search.search_method|intval},{$as_search.step_search|intval});

			as4Plugin.initSearchEngine();
		</script>
		<div class="clear"></div>
		</form>
		{include file=$as_obj->_getTplPath("pm_advancedsearch_footer_block.tpl")}
	{/foreach}
	{if isset($hideAS4Form) && $hideAS4Form == true}{literal}</div><script type="text/javascript">$(document).ready(function() { as4Plugin.moveFormContainerForSEOPages(); });</script>{/literal}{/if}
{/if}