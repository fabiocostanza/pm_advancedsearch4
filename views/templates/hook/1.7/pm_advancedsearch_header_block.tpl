{assign var=searchEngineTitle value=$as_search.title}
{if empty($searchEngineTitle)}
	{assign var=searchEngineTitle value={l s='Filters' mod='pm_advancedsearch4'}}
{/if}
{if $hookName eq 'leftcolumn' || $hookName eq 'rightcolumn'}
	<div id="PM_ASBlockOutput_{$as_search.id_search|intval}" class="PM_ASBlockOutput PM_ASBlockOutputVertical"{if empty($as_search.criterions)} style="display:none"{/if} data-id-search="{$as_search.id_search|intval}">
	<div id="PM_ASBlock_{$as_search.id_search|intval}" class="card">
		{if $searchEngineTitle}
			<div class="card-header">
				<span class="PM_ASBlockTitle">{$searchEngineTitle}{if $as_search.display_nb_result_on_blc} <small class="PM_ASBlockNbProductValue">({$as_search.total_products|intval} {if $as_search.total_products > 1}{l s='products' mod='pm_advancedsearch4'}{else}{l s='product' mod='pm_advancedsearch4'}{/if})</small>
				{/if}</span>
			</div>
		{/if}
		<div class="card-block">
{else}
	<div id="PM_ASBlockOutput_{$as_search.id_search|intval}" class="PM_ASBlockOutput PM_ASBlockOutputHorizontal {$as_search.css_classes}"{if empty($as_search.criterions)} style="display:none"{/if} data-id-search="{$as_search.id_search|intval}">
	<div id="PM_ASBlock_{$as_search.id_search|intval}" class="card">
		<div class="card-header{if empty($as_search.title)} hidden-sm-up{/if}">
			<span class="PM_ASearchTitle">{$searchEngineTitle}{if $as_search.display_nb_result_on_blc} <small class="PM_ASBlockNbProductValue">({$as_search.total_products|intval} {if $as_search.total_products > 1}{l s='products' mod='pm_advancedsearch4'}{else}{l s='product' mod='pm_advancedsearch4'}{/if})</small>{/if}</span>
		</div>
		<div class="card-block">
{/if}
