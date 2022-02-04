<div id="PM_ASearchResults" data-id-search="{$id_search|intval}">
	<div id="PM_ASearchResultsInner" class="PM_ASearchResultsInner_{$id_search|intval}">
		{include file="$tpl_dir./errors.tpl"}

		<h1 id="PM_ASearchResultsTitle" class="page-heading product-listing">
			<span class="cat-name">{strip}{$as_seo_title|escape:'htmlall':'UTF-8'}{/strip}</span>
			{include file="$tpl_dir./category-count.tpl"}
		</h1>
		{if $as_seo_description}
			<div class="cat_desc">{$as_seo_description|as4_nofilter}</div>
		{/if}
		{if !empty($as_searchs.0.remind_selection) && ($as_searchs.0.remind_selection == 3 || $as_searchs.0.remind_selection == 1)}
			{include file=$as_obj->_getTplPath("pm_advancedsearch_selection_results.tpl")}
		{/if}

		{if $products}
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
			{include file="$tpl_dir./product-list.tpl" products=$products}
			<div class="content_sortPagiBar">
				<div class="bottom-pagination-content clearfix">
					{include file="$tpl_dir./product-compare.tpl" paginationId='bottom'}
					{include file="$tpl_dir./pagination.tpl" paginationId='bottom'}
				</div>
			</div>
		{/if}
	</div>
</div>