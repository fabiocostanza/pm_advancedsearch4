<div class="form-group">
    <label class="control-label col-lg-4 col-sm-4">{$options.label|escape:'htmlall':'UTF-8'}</label>
    <div class="col-lg-8 col-sm-8">
        <input type="{$options.type|escape:'htmlall':'UTF-8'}" name="{$options.key|escape:'htmlall':'UTF-8'}" id="{$options.key|escape:'htmlall':'UTF-8'}" value="{$current_value|as4_nofilter}" style="width:{$options.size|escape:'htmlall':'UTF-8'}" class="ui-corner-all ui-input-pm"{if !empty($options.required)} required="required"{/if}{if !empty($options.onkeyup)} onkeyup="{$options.onkeyup|as4_nofilter}"{/if}{if !empty($options.onchange)} onchange="{$options.onchange|as4_nofilter}"{/if}{if $options.min !== false} min="{$options.min|intval}"{/if}{if $options.max !== false} max="{$options.max|intval}"{/if}{if $options.maxlength !== false} maxlength="{$options.maxlength|intval}"{/if} {if $options.placeholder !== false} placeholder="{$options.placeholder|escape:'htmlall':'UTF-8'}"{/if} />
        {include file='./tips.tpl' options=$options}
        {include file='../clear.tpl'}
    </div>
</div>
