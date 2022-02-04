<label>{$options.label|escape:'htmlall':'UTF-8'}</label>
<div class="margin-form">
    <input size="20" type="text" name="{$options.key|escape:'htmlall':'UTF-8'}" id="{$options.key|escape:'htmlall':'UTF-8'}" class="colorPickerInput ui-corner-all ui-input-pm" value="{$current_value|as4_nofilter}" style="width:{$options.size|escape:'htmlall':'UTF-8'}" />
	{include file='./tips.tpl' options=$options}
    {include file='../clear.tpl'}
</div>