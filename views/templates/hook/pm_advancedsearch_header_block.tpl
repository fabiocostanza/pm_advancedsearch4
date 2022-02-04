{assign var=searchEngineTitle value=$as_search.title}
{if empty($searchEngineTitle)}
	{assign var=searchEngineTitle value={l s='Filters' mod='pm_advancedsearch4'}}
{/if}
{if $hookName eq 'leftcolumn' || $hookName eq 'rightcolumn'}
	{if empty($ajaxMode)}
		<div id="PM_ASBlockOutput_{$as_search.id_search|intval}" class="PM_ASBlockOutput PM_ASBlockOutputVertical block"{if isset($as_search.criterions) && is_array($as_search.criterions) && !sizeof($as_search.criterions)} style="display:none"{/if} data-id-search="{$as_search.id_search|intval}">
	{/if}
	<div id="PM_ASBlock_{$as_search.id_search|intval}">
		{if $searchEngineTitle}
			<p class="title_block">
				<span class="PM_ASBlockTitle">{$searchEngineTitle|escape:'htmlall':'UTF-8'}</span>
				{if $as_search.display_nb_result_on_blc}
					<span class="PM_ASBlockNbProductValue">
						({$as_search.total_products|intval} {if $as_search.total_products > 1}{l s='products' mod='pm_advancedsearch4'}{else}{l s='product' mod='pm_advancedsearch4'}{/if})
					</span>
				{/if}
			</p>
		{/if}
		<div class="block_content">
{else}
	{if empty($ajaxMode)}
		<div id="PM_ASBlockOutput_{$as_search.id_search|intval}" class="PM_ASBlockOutput PM_ASBlockOutputHorizontal {$as_search.css_classes|escape:'htmlall':'UTF-8'}"{if isset($as_search.criterions) && is_array($as_search.criterions) && !sizeof($as_search.criterions)} style="display:none"{/if} data-id-search="{$as_search.id_search|intval}">
	{/if}
	<div id="PM_ASBlock_{$as_search.id_search|intval}">
		{if $searchEngineTitle}
			<h4 class="PM_ASearchTitle">{$searchEngineTitle|escape:'htmlall':'UTF-8'}{if $as_search.display_nb_result_on_blc} <small class="PM_ASBlockNbProductValue">({$as_search.total_products|intval} {if $as_search.total_products > 1}{l s='products' mod='pm_advancedsearch4'}{else}{l s='product' mod='pm_advancedsearch4'}{/if})</small>{/if}</h4>
		{/if}
		<div class="block_content">
{/if}