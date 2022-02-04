<label>{$options.label|escape:'htmlall':'UTF-8'}</label>
<div class="margin-form">
    {foreach from=$languages item=language}
        <div id="lang{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}" class="pmFlag pmFlagLang_{$language.id_lang|intval}" style="display: {if $language.id_lang == $default_language}block{else}none{/if}; float: left;">
            <textarea class="rte" rows="10" name="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}" id="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}" style="width:{$options.size|escape:'htmlall':'UTF-8'}">{$current_value[$language.id_lang]|as4_nofilter}</textarea>
        </div>
    {/foreach}

    {$pm_flags|as4_nofilter}

    {include file='./tips.tpl' options=$options}
    {include file='../clear.tpl'}
</div>