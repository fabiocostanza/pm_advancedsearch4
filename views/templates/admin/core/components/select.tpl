<div class="form-group">
    {if !empty($options.label)}
        <label class="control-label col-lg-4 col-sm-4">{$options.label|escape:'htmlall':'UTF-8'}</label>
        <div class="pm_displaySelect col-lg-8 col-sm-8">
    {else}
        <div class="pm_displaySelect col-lg-8 col-sm-8">
    {/if}
        <select id="{$options.key|escape:'htmlall':'UTF-8'}" name="{$options.key|escape:'htmlall':'UTF-8'}" style="width:{$options.size|escape:'htmlall':'UTF-8'}">
            {if !empty($options.defaultvalue)}
                <option value="0">{$options.defaultvalue|escape:'htmlall':'UTF-8'}</option>
            {/if}
            {foreach from=$options.options key=value item=text_value}
                <option value="{$value|escape:'htmlall':'UTF-8'}" {$selected_attr[$value]|escape:'htmlall':'UTF-8'} {if !empty($options.class[$value])} class="{$options.class[$value]|escape:'htmlall':'UTF-8'}"{/if}>{$text_value|stripslashes}</option>
            {/foreach}
        </select>
        <script type="text/javascript">
            {if !empty($options.onchange)}
                $("#{$options.key|escape:'htmlall':'UTF-8'}").unbind("change").bind("change",function() {
                    {$options.onchange|as4_nofilter}
                });
            {/if}
        </script>
        {include file='./tips.tpl' options=$options}
        {include file='../clear.tpl'}
    </div>
</div>
