<label>{$options.label|escape:'htmlall':'UTF-8'}</label>
<div class="margin-form">
    <select id="multiselect{$options.key|escape:'htmlall':'UTF-8'}" class="multiselect" multiple="multiple" name="{$options.key|escape:'htmlall':'UTF-8'}[]">
    {if isset($options.selectedoptions) && $options.selectedoptions|is_array && $options.selectedoptions|sizeof}
        {foreach from=$options.selectedoptions key=option_value item=selected_option}
            {if $index_column}
                <option value="{if $selected_option[$options.idcolumn]|is_numeric}{$selected_option[$options.idcolumn]|intval}{else}{$selected_option[$options.idcolumn]|as4_nofilter}{/if}" selected="selected">{$selected_option[$options.namecolumn]|escape:'html':'UTF-8'}</option>
            {else}
                <option value="{if $option_value|is_numeric}{$option_value|intval}{else}{$option_value|as4_nofilter}{/if}" selected="selected">{$selected_option|escape:'html':'UTF-8'}</option>
            {/if}
        {/foreach}
    {/if}
    </select>

    {include file='./tips.tpl' options=$options}
    {include file='../clear.tpl'}
</div>
{include file='../clear.tpl'}

<script type="text/javascript">
    $("#multiselect{$options.key|escape:'htmlall':'UTF-8'}").multiselect({
        locale: {
            addAll: {{l s='Add all' mod='pm_advancedsearch4'}|json_encode},
            removeAll: {{l s='Remove all' mod='pm_advancedsearch4'}|json_encode},
            itemsCount: {{l s='#{count} items selected' mod='pm_advancedsearch4'}|json_encode},
            itemsTotal: {{l s='#{count} items total' mod='pm_advancedsearch4'}|json_encode},
            busy: {{l s='Please wait...' mod='pm_advancedsearch4'}|json_encode},
            errorDataFormat: {{l s='Cannot add options, unknown data format' mod='pm_advancedsearch4'}|json_encode},
            errorInsertNode: {{l s='There was a problem trying to add the item: [#{key}] - #{value}. The operation was aborted.' mod='pm_advancedsearch4'}|json_encode},
            errorReadonly: {{l s='The option #{option} is readonly' mod='pm_advancedsearch4'}|json_encode},
            errorRequest: {{l s='Sorry! There seemed to be a problem with the remote call. (Type: #{status})' mod='pm_advancedsearch4'}|json_encode},
            sInputSearch: {{l s='Enter your search query here' mod='pm_advancedsearch4'}|json_encode},
            sInputShowMore: {{l s='Show more' mod='pm_advancedsearch4'}|json_encode},
        },
        remoteUrl: {$options.remoteurl|json_encode},
        remoteLimit: {$options.limit|intval},
        remoteStart: 0,
        remoteLimitIncrement: {$options.limitincrement|intval},
        {if !empty($options.remoteparams)}
            remoteParams: { {$options.remoteparams|as4_nofilter} }
        {/if}
        triggerOnLiClick: {if !empty($options.triggeronliclick)}true{else}false{/if},
        displayMore: {if !empty($options.displaymore)}true{else}false{/if}
    });
</script>