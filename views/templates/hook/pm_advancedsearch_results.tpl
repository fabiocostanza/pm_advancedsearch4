
<div id="PM_ASearchResults" data-id-search="{$as_searchs.0.id_search|intval}">
<div id="PM_ASearchResultsInner" class="PM_ASearchResultsInner_{$as_searchs.0.id_search|intval}">
{if isset($as_searchs[0]) && !$as_searchs.0.keep_category_information}
	{if isset($as_seo_title)}
		{capture name=path}{$as_seo_title|escape:'htmlall':'UTF-8'}{/capture}
	{else}
		{capture name=path}{$as_searchs.0.title|escape:'htmlall':'UTF-8'}{/capture}
	{/if}
{/if}
{include file="$tpl_dir./errors.tpl"}

{if isset($as_searchs[0])}
	{if $as_searchs.0.id_search AND $as_searchs.0.active}
		{if isset($as_seo_title)}
			<h1 id="PM_ASearchResultsTitle" class="page-heading product-listing">
				<span class="cat-name">{strip}{$as_seo_title|escape:'htmlall':'UTF-8'}{/strip}</span>
				{strip}
				<span class="heading-counter">
				{if $as_searchs.0.total_products == 0}
					{l s='There are no products in  this category' mod='pm_advancedsearch4'}
				{else}
					{if $as_searchs.0.total_products == 1}
						{l s='There is %d product.' sprintf=$as_searchs.0.total_products mod='pm_advancedsearch4'}
					{else}
						{l s='There are %d products.' sprintf=$as_searchs.0.total_products mod='pm_advancedsearch4'}
					{/if}
				{/if}
				</span>
				{/strip}
			</h1>
			{if $as_seo_description}
				<div class="cat_desc">{$as_seo_description|as4_nofilter}</div>
			{/if}
		{else}
			{if !$as_searchs.0.keep_category_information}
				<h1 id="PM_ASearchResultsTitle" class="page-heading product-listing">
					<span class="cat-name">{strip}{$as_searchs.0.title|escape:'htmlall':'UTF-8'}{/strip}</span>
					{include file="$tpl_dir./category-count.tpl"}
				</h1>
				{if $as_searchs.0.description}
					<div class="cat_desc">{$as_searchs.0.description|as4_nofilter}</div>
				{/if}
			{/if}
		{/if}
		{include file="$tpl_dir./errors.tpl"}
		{if $as_searchs.0.remind_selection == 3 OR $as_searchs.0.remind_selection == 1}
			{include file=$as_obj->_getTplPath("pm_advancedsearch_selection_results.tpl")}
		{/if}
		{if $as_searchs.0.products}
			<div class="content_sortPagiBar clearfix">
				<div class="sortPagiBar clearfix">
					{include file="$tpl_dir./product-sort.tpl"}
					{include file="$tpl_dir./nbr-product-page.tpl"}
				</div>
				<div class="top-pagination-content clearfix">
						{include file="$tpl_dir./product-compare.tpl"}
						{include file="$tpl_dir./pagination.tpl"}
				</div>
			</div>
			{include file="$tpl_dir./product-list.tpl" products=$as_searchs.0.products}
			
			<div class="content_sortPagiBar">
				<div class="bottom-pagination-content clearfix">
					{include file="$tpl_dir./product-compare.tpl" paginationId='bottom'}
					{include file="$tpl_dir./pagination.tpl" paginationId='bottom'}
				</div>
			</div>
		{else}
			<p class="alert alert-warning">{l s='There are no result.' mod='pm_advancedsearch4'}</p>
		{/if}
		{include file=$as_obj->_getTplPath("pm_advancedsearch_cross_links.tpl")}
	{/if}
	<script type="text/javascript">
		as4Plugin.params[{$as_searchs.0.id_search|intval}] = {ldelim}
			'hookName'						: '{$hookName|escape:'htmlall':'UTF-8'}',
			'centerColumnCssClasses'		: {$as_searchs.0.search_results_selector_css|json_encode},
			'availableCriterionsGroups'		: {if isset($as_searchs.0.criterionsGroupsMini)}{$as_searchs.0.criterionsGroupsMini|json_encode}{else}{ldelim}{rdelim}{/if},
			'selectedCriterions'			: {if isset($as_searchs.0.criterions_selected)}{$as_searchs.0.criterions_selected|json_encode}{else}{ldelim}{rdelim}{/if},
			'stepSearch' 					: {$as_searchs.0.step_search|intval},
			'searchMethod' 					: {$as_searchs.0.search_method|intval},
			'keep_category_information' 	: {if As4SearchEngine::getCurrentCategory() || As4SearchEngine::getCurrentManufacturer() || As4SearchEngine::getCurrentSupplier() || !empty($smarty.get.seo_url)}{$as_searchs.0.keep_category_information|intval}{else}0{/if},
			'search_results_selector'		: '{$as_searchs.0.search_results_selector|escape:'htmlall':'UTF-8'}',
			'insert_in_center_column'		: {$as_searchs.0.insert_in_center_column|intval},
			'seo_criterion_groups'			: '{if isset($as_searchs.0.seo_criterion_groups) && is_array($as_searchs.0.seo_criterion_groups)}{","|implode:$as_searchs.0.seo_criterion_groups|escape:'htmlall':'UTF-8'}{/if}',
			'as4_productFilterListData'		: {if isset($as4_productFilterListData) && !empty($as4_productFilterListData)}{$as4_productFilterListData|json_encode}{else}''{/if},
			'as4_productFilterListSource'	: {if isset($as4_productFilterListSource) && !empty($as4_productFilterListSource)}{$as4_productFilterListSource|json_encode}{else}''{/if},
			'scrollTopActive'				: {if isset($as_searchs.0.scrolltop_active) && $as_searchs.0.scrolltop_active}true{else}false{/if},
			'resetURL'						: {if is_array($as_searchs.0.selected_criterion) && sizeof($as_searchs.0.selected_criterion)}{As4SearchEngine::generateURLFromCriterions($as_searchs.0.id_search)|json_encode}{else}''{/if}
		{rdelim};
		{if !empty($as4_localCacheKey)}
			as4Plugin.localCacheKey = {$as4_localCacheKey|json_encode};
		{/if}
		{if isset($as4_localCache)}
			as4Plugin.localCache = {$as4_localCache|json_encode};
		{/if}
		as4Plugin.as4_blurEffect = {$as4_blurEffect|json_encode};
		{if !empty($orderby)}
			as4Plugin.params[{$as_searchs.0.id_search|intval}].orderBy = {$orderby|escape:'htmlall':'UTF-8'|json_encode};
		{/if}
		{if !empty($orderway)}
			as4Plugin.params[{$as_searchs.0.id_search|intval}].orderWay = {$orderway|escape:'htmlall':'UTF-8'|json_encode};
		{/if}
		as4Plugin.params[{$as_searchs.0.id_search|intval}].addBestSalesOption = true;

		as4Plugin.initSearchFromResults({$as_searchs.0.id_search|intval},{$as_searchs.0.search_method|intval},{$as_searchs.0.step_search|intval});
	</script>
{/if}

</div>
</div>
