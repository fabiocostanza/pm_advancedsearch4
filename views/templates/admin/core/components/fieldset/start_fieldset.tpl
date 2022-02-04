<fieldset>
{if !empty($options.title) || !empty($options.hide)}
	<legend class="ui-state-default" style="cursor:pointer;" onclick="$(this).next('div').slideToggle('fast');{if !empty($options.onclick)}{$options.onclick|as4_nofilter}{/if}">
		{if !empty($options.icon)}
			<img src="{$options.icon|as4_nofilter}" alt="{$options.title|escape:'htmlall':'UTF-8'}" title="{$options.title|escape:'htmlall':'UTF-8'}" />
		{/if}
		<span>{$options.title|escape:'htmlall':'UTF-8'}</span>
		<small {if empty($options.hide)}style="display:none;"{/if}>{l s='Click here to open' mod='pm_advancedsearch4'}</small>
	</legend>
{/if}
	<div{if !empty($options.hide)}class="hideAfterLoad"{/if}>