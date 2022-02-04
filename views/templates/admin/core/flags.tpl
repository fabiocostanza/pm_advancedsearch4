{if !empty($class)}<div class="{$class|escape:'htmlall':'UTF-8'}">{/if}

<select id="{$flag_key|escape:'htmlall':'UTF-8'}" style="width:50px;" class="pmSelectFlag ui-select-pm">
{foreach from=$languages item=language}
    <option value="{$language.id_lang|intval}" class="pmFlag_{$language.id_lang|intval}" {if $language.id_lang == $default_language}selected="selected"{else}selected=""{/if}>{$language.iso_code|strtoupper|escape:'htmlall':'UTF-8'}</option>
{/foreach}
</select>

{if !empty($class)}</div>{/if}

<script type="text/javascript">
    initFlags("{$flag_key|escape:'htmlall':'UTF-8'}", {$default_language|intval});
</script>