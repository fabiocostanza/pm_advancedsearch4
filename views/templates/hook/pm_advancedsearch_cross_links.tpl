{if isset($as_cross_links) && $as_cross_links && sizeof($as_cross_links)}
	<br />
	<br />
	<div id="PM_ASearchSeoCrossLinks" class="block">
		<h4>{l s='See also' mod='pm_advancedsearch4'}</h4>
		<div class="block_content">
			<ul class="bullet">
			{foreach from=$as_cross_links item=as_cross_link}
				<li>
					<a href="{$as_cross_link.public_url|as4_nofilter}">{$as_cross_link.title|escape:'htmlall':'UTF-8'}</a>
				</li>
			{/foreach}
			</ul>
		</div>
	</div>
{/if}