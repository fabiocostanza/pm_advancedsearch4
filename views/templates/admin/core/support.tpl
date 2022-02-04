<div class="pm_footer_container ui-corner-all ui-tabs ui-tabs-panel">
    <div id="pm_support_informations" class="pm_panel_bottom">
        {include file='./title.tpl' text={l s='Useful links' mod='pm_advancedsearch4'}}

        <ul class="pm_links_block">
            <li class="pm_module_version"><strong>{l s='Module Version: ' mod='pm_advancedsearch4'}</strong> {$pm_module_version|escape:'htmlall':'UTF-8'}</li>

        {if (isset($getting_started) && $getting_started)}
            <li class="pm_get_started_link"><a href="javascript:;" class="pm_link">{l s='Getting started' mod='pm_advancedsearch4'}</a></li>
        {/if}

        {if isset($support_links) && $support_links|is_array && $support_links|sizeof}
            {foreach from=$support_links item=support_link}
                <li class="pm_useful_link"><a href="{$support_link.link|as4_nofilter}" target="_blank" class="pm_link">{$support_link.label|escape:'htmlall':'UTF-8'}</a></li>
            {/foreach}
        {/if}
        </ul>

        {if isset($copyright_link) && $copyright_link|is_array && $copyright_link|sizeof}
            <div class="pm_copy_block">
            {if (isset($copyright_link.link) && $copyright_link.link != '')}
                <a href="{$copyright_link.link|as4_nofilter}"{if isset($copyright_link.target)} target="{$copyright_link.target|escape:'htmlall':'UTF-8'}"{/if}{if isset($copyright_link.style)} style="{$copyright_link.style|escape:'htmlall':'UTF-8'}"{/if}
                >
            {/if}
            <img src="{$copyright_link.img|as4_nofilter}" />
            {if (isset($copyright_link.link) && $copyright_link.link != '')}
                </a>
            {/if}
            </div>
        {/if}
    </div>

    {if isset($get_started_image_list) && $get_started_image_list|is_array && $get_started_image_list|sizeof}
        <script type="text/javascript">
            $('.pm_get_started_link a').click(function() {
                $.fancybox([ {$get_started_image_list|implode:','} ], {
                    'padding'           : 0,
                    'transitionIn'      : 'none',
                    'transitionOut'     : 'none',
                    'type'              : 'image',
                    'changeFade'        : 0
                });
            });
        </script>
    {/if}
</div>
<div class="pm_footer_container ui-corner-all ui-tabs ui-tabs-panel">
    {include file="./cs-addons.tpl"}
</div>