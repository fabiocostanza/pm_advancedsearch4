<div class="form-group">
    {if !empty($options.label)}
        <label class="control-label col-lg-4 col-sm-4">{$options.label|escape:'htmlall':'UTF-8'}</label>
    {/if}
    <div class="col-lg-8 col-sm-8">
        {foreach from=$languages item=language}
            <div id="lang{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}" class="pmFlag pmFlagLang_{$language.id_lang|intval}" style="display: {if $language.id_lang == $default_language}block{else}none{/if};">
                <input type="{$options.type|escape:'htmlall':'UTF-8'}" name="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}" id="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}" value="{$current_value[$language.id_lang]|as4_nofilter}" style="margin-right:1rem;width:{$options.size|escape:'htmlall':'UTF-8'}" class="ui-corner-all ui-input-pm"{if !empty($options.required) && $language.id_lang == $default_language} required="required"{/if}{if !empty($options.onkeyup)} onkeyup="{$options.onkeyup|as4_nofilter}"{/if}{if !empty($options.onchange)} onchange="{$options.onchange|as4_nofilter}"{/if}{if $options.min !== false && $language.id_lang == $default_language} min="{$options.min|intval}"{/if}{if $options.max !== false && $language.id_lang == $default_language} max="{$options.max|intval}"{/if}{if $options.maxlength !== false && $language.id_lang == $default_language} maxlength="{$options.maxlength|intval}"{/if} {if $options.placeholder !== false} placeholder="{$options.placeholder|escape:'htmlall':'UTF-8'}"{/if} />
            </div>
        {/foreach}

        {$pm_flags|as4_nofilter}

        {include file='./tips.tpl' options=$options}
        {include file='../clear.tpl'}
    </div>
</div>
