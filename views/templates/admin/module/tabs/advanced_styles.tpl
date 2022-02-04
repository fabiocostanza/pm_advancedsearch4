<form action="{$base_config_url|as4_nofilter}#config-2" id="formAdvancedStyles_{$module_name|escape:'htmlall':'UTF-8'}" name="formAdvancedStyles_{$module_name|escape:'htmlall':'UTF-8'}" method="post">
    <div class="dynamicTextarea">
        <textarea name="advancedConfig" id="advancedConfig" cols="120" rows="30">{$advanced_styles|as4_nofilter}</textarea>
    </div>
    {include file="../../core/clear.tpl"}
    <br />
    <center>
        <input type="submit" value="{l s='Save' mod='pm_advancedsearch4'}" name="submitAdvancedConfig" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" />
    </center>
</form>
<script type="text/javascript">
    var editor = CodeMirror.fromTextArea(document.getElementById("advancedConfig"), {});
</script>
{include file="../../core/clear.tpl"}