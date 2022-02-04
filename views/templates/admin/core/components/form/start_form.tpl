<form action="{$form_action|escape:'htmlall':'UTF-8'}" method="post" class="width3" id="{$form_id|escape:'htmlall':'UTF-8'}" target="{$form_target|escape:'htmlall':'UTF-8'}">
{if !empty($obj_id)}
<input type="hidden" name="{$obj_identifier}" value="{$obj_id|as4_nofilter}" />
{/if}
{if !empty($obj_class)}
<input type="hidden" name="pm_save_obj" value="{$obj_class|escape:'htmlall':'UTF-8'}" />
{/if}
{if !empty($pm_reload_after)}
<input type="hidden" name="pm_reload_after" value="{$pm_reload_after|as4_nofilter}" />
{/if}
{if !empty($pm_js_callback)}
<input type="hidden" name="pm_js_callback" value="{$pm_js_callback|as4_nofilter}" />
{/if}