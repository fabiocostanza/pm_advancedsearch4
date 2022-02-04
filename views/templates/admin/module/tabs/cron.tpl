{module->_showInfo text="{l s='Heavy catalog may need asynchronous/periodical reindex.' mod='pm_advancedsearch4'}<br />{l s='You will find below the needed URL to run this reindex process.' mod='pm_advancedsearch4'}<br />{l s='If you don\'t known how to implement this, please ask your hoster to do it for you.' mod='pm_advancedsearch4'}"}

{module->_displaySubTitle text="{l s='Reindex all search engines:' mod='pm_advancedsearch4'}"}
<ul>
	<li>
		<a href="{$main_cron_url|escape:'htmlall':'UTF-8'}" target="_blank">{$main_cron_url|escape:'htmlall':'UTF-8'}</a>
	</li>
</ul>

{if !empty($cron_urls) && $cron_urls|is_array && $cron_urls|sizeof}
	{module->_displaySubTitle text="{l s='Reindex a specific search engine:' mod='pm_advancedsearch4'}"}
	<ul>
	{foreach from=$cron_urls item=cron_url}
		<li>
			<a href="{$cron_url|escape:'htmlall':'UTF-8'}" target="_blank">{$cron_url|escape:'htmlall':'UTF-8'}</a>
		</li>
	{/foreach}
	</ul>
{/if}