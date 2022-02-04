{as4_startForm id="SEOpages"}

{module->_displayTitle text="{l s='URL' mod='pm_advancedsearch4'}"}
{module->_showInfo text="{l s='Use this URL in your slideshows, menus and others. One link per language is available.' mod='pm_advancedsearch4'}"}

<div class="margin-form" style="padding-left:100px;">
    {foreach from=$seo_url_by_lang key=id_lang item=seo_url}
        <div id="langseo_url_{$id_lang|intval}" class="pmFlag pmFlagLang_{$id_lang|intval}" style="display: {if $id_lang == $default_language}block{else}none{/if}; float: left;">
            <input size="130" type="text" name="seo_url_{$id_lang|intval}" onfocus="this.select();" value="{$seo_url|escape:'htmlall':'UTF-8'}" />
        </div>
    {/foreach}

    {$pm_flags|as4_nofilter}

    {include file='../../core/clear.tpl'}
</div>

{as4_endForm id="SEOpages"}