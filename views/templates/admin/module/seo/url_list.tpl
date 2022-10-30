{module->displayTitle text="{l s='URL list' mod='pm_advancedsearch4'}"}

{if $seo_url_list|is_array && $seo_url_list|sizeof}
    {as4_startForm id="SEOURLListForm"}

    {module->showInfo text="{l s='Here you will find the list of URLs of all SEO pages (facets) associated with this search engine. You can add these URLs in your menu, slideshows, footer or blocks so that Google can index them more easily.' mod='pm_advancedsearch4'}"}
    <p>{l s='Please select the language:' mod='pm_advancedsearch4'}</p>
    {$pm_flags|as4_nofilter}
    {include file='../../core/clear.tpl'}

    {foreach from=$seo_url_list key=id_lang item=seo_urls}
        <div id="langseo_url_{$id_lang|intval}" class="pmFlag pmFlagLang_{$id_lang|intval}" style="display: {if $id_lang == $default_language}block{else}none{/if};">
            <h3>{l s='HTML version:' mod='pm_advancedsearch4'}</h3>
            {strip}
            <textarea rows="10" style="width:100%">
                {"<ul>\n"|escape:'htmlall':'UTF-8'}
                {foreach from=$seo_urls item=seo_url}
                    {"\t<li><a"|escape:'htmlall':'UTF-8'} href="{$seo_url.url|as4_nofilter}" title="{$seo_url.title|escape:'htmlall':'UTF-8'}"{">"|escape:'htmlall':'UTF-8'}{$seo_url.title|escape:'htmlall':'UTF-8'}{"</a></li>\n"|escape:'htmlall':'UTF-8'}
                {/foreach}
                {"</ul>"|escape:'htmlall':'UTF-8'}
            </textarea>
            {/strip}
            <h3>{l s='CSV version:' mod='pm_advancedsearch4'}</h3>
            {strip}
            <textarea rows="10" style="width:100%">
                {foreach from=$seo_urls item=seo_url}"{$seo_url.title|as4_nofilter}";"{$seo_url.url|as4_nofilter}"{"\n"}{/foreach}
            </textarea>
            {/strip}
        </div>
    {/foreach}

    {as4_startForm id="SEOURLListForm"}
{else}
    {module->showInfo text="{l s='No URL yet' mod='pm_advancedsearch4'}"}
{/if}
