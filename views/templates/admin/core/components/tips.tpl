{if !empty($options.tips)}
    {if !empty($options.key_active)}
        {assign var='tips_key' value=$options.key_active}
    {else}
        {assign var='tips_key' value=$options.key}
    {/if}
    <img title="{$options.tips|escape:'htmlall':'UTF-8'}" id="{$tips_key|escape:'htmlall':'UTF-8'}-tips" class="pm_tips" src="{$module_path|as4_nofilter}views/img/question.png" width="16px" height="16px" {if !empty($nofloat)}style="float: none!important"{/if} />
    <script type="text/javascript">initTips("#{$tips_key|escape:'htmlall':'UTF-8'}")</script>
{/if}