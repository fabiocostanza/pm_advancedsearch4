{as4_startForm id="seoRegenerateForm"}

{module->_displayTitle text="{l s='Regenerate SEO metas' mod='pm_advancedsearch4'}"}
{module->_showInfo text="{l s='Select the type of data you want to regenerate, then click on “Regenerate“ button' mod='pm_advancedsearch4'}"}

<input type="hidden" name="id_search" id="id_search" value="{$id_search|intval}" />
<div id="buttonSetSeoRegenerate">
    <input type="checkbox" name="fields_to_regenerate[]" id="check1" value="meta_title" /> <label for="check1">{l s='Meta title' mod='pm_advancedsearch4'}</label>
    <input type="checkbox" name="fields_to_regenerate[]" id="check2" value="meta_description" /> <label for="check2">{l s='Meta description' mod='pm_advancedsearch4'}</label>
    <input type="checkbox" name="fields_to_regenerate[]" id="check3" value="meta_keywords" /> <label for="check3">{l s='Meta keywords' mod='pm_advancedsearch4'}</label>
    <input type="checkbox" name="fields_to_regenerate[]" id="check4" value="title" /> <label for="check4">{l s='Title (H1)' mod='pm_advancedsearch4'}</label>
    <input type="checkbox" name="fields_to_regenerate[]" id="check5" value="seo_url" /> <label for="check5">{l s='Friendly URL' mod='pm_advancedsearch4'}</label>
</div>

{include file='../../core/clear.tpl'}
<br />
{module->_displaySubmit text="{l s='Regenerate' mod='pm_advancedsearch4'}" name="submitSeoRegenerate"}

<script type="text/javascript">
    $(document).ready(function(){
        $("#buttonSetSeoRegenerate").buttonset();
    });
</script>

{as4_startForm id="seoRegenerateForm"}