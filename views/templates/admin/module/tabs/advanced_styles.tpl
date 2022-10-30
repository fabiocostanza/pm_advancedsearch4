<form class="defaultForm form-horizontal" action="{$base_config_url|as4_nofilter}#config-2" id="formAdvancedStyles_{$module_name|escape:'htmlall':'UTF-8'}" name="formAdvancedStyles_{$module_name|escape:'htmlall':'UTF-8'}" method="post">
    {module->displaySubTitle text="{l s='Advanced Styles' mod='pm_advancedsearch4'}"}

    <div class="dynamicTextarea">
        <textarea name="advancedConfig" id="advancedConfig" cols="120" rows="30">{$advanced_styles|as4_nofilter}</textarea>
    </div>
    {include file="../../core/clear.tpl"}
    <br />
    <center>
        {module->displaySubmit text="{l s='Save' d='Admin.Actions'}" name='submitAdvancedConfig'}
    </center>
</form>
<script type="text/javascript">
    var editor = CodeMirror.fromTextArea(document.getElementById("advancedConfig"), {});
</script>
{include file="../../core/clear.tpl"}
