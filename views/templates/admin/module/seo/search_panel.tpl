{module->_displayTitle text="{l s='Predefined results page (SEO)' mod='pm_advancedsearch4'}"}
{if !empty($rewrite_settings)}
    {if !empty($seo_searchs) && $seo_searchs|is_array && $seo_searchs|sizeof}
        <div class="seoGsiteMapUrl">
            {module->_showInfo text="{l s='The Advanced Search sitemap is located at:' mod='pm_advancedsearch4'} <a target=\"_blank\" href=\"{$sitemap_url}\">{$sitemap_url}</a>"}
        </div>
    {/if}

    {as4_button text={l s='Add a new results page' mod='pm_advancedsearch4'} href="{$base_config_url|as4_nofilter}&pm_load_function=displaySeoSearchForm&class=AdvancedSearchSeoClass&pm_js_callback=closeDialogIframe&id_search={$id_search|intval}" class='open_on_dialog_iframe' rel='980_530_1' icon_class='ui-icon ui-icon-plusthick'}
    {as4_button text={l s='Massive add of results pages' mod='pm_advancedsearch4'} href="{$base_config_url|as4_nofilter}&pm_load_function=displayMassSeoSearchForm&pm_js_callback=closeDialogIframe&id_search={$id_search|intval}" class='open_on_dialog_iframe' rel='980_530_1' icon_class='ui-icon ui-icon-plusthick'}

    {if !empty($seo_searchs) && $seo_searchs|is_array && $seo_searchs|sizeof}
        <hr class="clear" />
        {as4_button text={l s='Remove empty results pages' mod='pm_advancedsearch4'} href="{$base_config_url|as4_nofilter}&pm_load_function=processRemoveEmptySeo&id_search={$id_search|intval}" class='ajax_script_load' icon_class='ui-icon ui-icon-trash'}
        {as4_button text={l s='Regenerate SEO meta data' mod='pm_advancedsearch4'} href="{$base_config_url|as4_nofilter}&pm_load_function=displaySeoRegenerateForm&id_search={$id_search|intval}" class='open_on_dialog_iframe' rel='700_300' icon_class='ui-icon ui-icon-refresh'}
        {as4_button text={l s='List all URLs' mod='pm_advancedsearch4'} href="{$base_config_url|as4_nofilter}&pm_load_function=displaySeoUrlList&id_search={$id_search|intval}" class='open_on_dialog_iframe' rel='980_530_1' icon_class='ui-icon ui-icon-link'}

        <table cellspacing="0" cellpadding="0" id="dataTable{$id_search|intval}" style="width:100%;">
            <thead>
                <tr>
                    <th width="10"></th>
                    <th width="50">{l s='Id' mod='pm_advancedsearch4'}</th>
                    <th style="width:auto">{l s='Title' mod='pm_advancedsearch4'}</th>
                    <th style="text-align:center;">{l s='Num. products' mod='pm_advancedsearch4'}</th>
                    <th style="text-align:center;">{l s='Edit' mod='pm_advancedsearch4'}</th>
                    <th style="text-align:center;">{l s='URL' mod='pm_advancedsearch4'}</th>
                    <th style="text-align:center;">{l s='Delete' mod='pm_advancedsearch4'}</th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$seo_searchs item=seo_search}
                <tr>
                    <td><input type="checkbox" name="seo_group_action[]" value="{$seo_search.id_seo|intval}" /></td>
                    <td>{$seo_search.id_seo|intval}</td>
                    <td>{$seo_search.title|escape:'htmlall':'UTF-8'}</td>
                    <td style="text-align:center;">
                        <strong{if empty($seo_search.total_products)} style="color: #cc0000"{/if}>{$seo_search.total_products|intval}</strong>
                    </td>
                    <td style="text-align:center;">
                        {as4_button text='' href="{$base_config_url|as4_nofilter}&pm_load_function=displaySeoSearchForm&class=AdvancedSearchSeoClass&pm_js_callback=closeDialogIframe&id_search={$id_search|intval}&id_seo={$seo_search.id_seo|intval}" class='open_on_dialog_iframe' rel='980_530_1' icon_class='ui-icon ui-icon-pencil'}
                    </td>
                    <td style="text-align:center;">
                        {as4_button text='' href="{$base_config_url|as4_nofilter}&pm_load_function=displaySeoUrl&id_search={$id_search|intval}&id_seo={$seo_search.id_seo|intval}" class='open_on_dialog_iframe' rel='980_530_1' icon_class='ui-icon ui-icon-link'}
                    </td>
                    <td style="text-align:center;">
                        {as4_button text='' href="{$base_config_url|as4_nofilter}&pm_load_function=processDeleteSeoSearch&id_search={$id_search|intval}&id_seo={$seo_search.id_seo|intval}" class='ajax_script_load pm_confirm' title={l s='Delete item #%d ?' mod='pm_advancedsearch4' sprintf=$seo_search.id_seo} icon_class='ui-icon ui-icon-trash'}
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
        <br />

        {as4_button text={l s='Check/uncheck all' mod='pm_advancedsearch4'} onclick="checkAllSeoItems({$id_search|intval});" icon_class='ui-icon ui-icon-check'}
        {as4_button text={l s='Delete selected items' mod='pm_advancedsearch4'} onclick="deleteSeoItems({$id_search|intval});" icon_class='ui-icon ui-icon-trash'}

        <script type="text/javascript">
        $(document).ready(function() {
            loadAjaxLink();
            var oTable = $('#dataTable{$id_search|intval}').dataTable({
                "sDom": 'R<"H"lfr>t<"F"ip<',
                "bJQueryUI": true,
                "sPaginationType": "full_numbers",
                "bStateSave": true,
                "oLanguage": {
                    "sLengthMenu": {{l s='Display _MENU_ records per page' mod='pm_advancedsearch4'}|json_encode},
                    "sZeroRecords": {{l s='Nothing found - sorry' mod='pm_advancedsearch4'}|json_encode},
                    "sInfo": {{l s='Showing _START_ to _END_ of _TOTAL_ records' mod='pm_advancedsearch4'}|json_encode},
                    "sInfoEmpty": {{l s='Showing 0 to 0 of 0 records' mod='pm_advancedsearch4'}|json_encode},
                    "sInfoFiltered": {{l s='(filtered from _MAX_ total records)' mod='pm_advancedsearch4'}|json_encode},
                    "sPageNext": {{l s='Next' mod='pm_advancedsearch4'}|json_encode},
                    "sPagePrevious": {{l s='Previous' mod='pm_advancedsearch4'}|json_encode},
                    "sPageLast": {{l s='Last' mod='pm_advancedsearch4'}|json_encode},
                    "sPageFirst": {{l s='First' mod='pm_advancedsearch4'}|json_encode},
                    "sSearch": {{l s='Search' mod='pm_advancedsearch4'}|json_encode},
                    "sFirst": {{l s='First' mod='pm_advancedsearch4'}|json_encode},
                    "sPrevious": {{l s='Previous' mod='pm_advancedsearch4'}|json_encode},
                    "sNext": {{l s='Next' mod='pm_advancedsearch4'}|json_encode},
                    "sLast": {{l s='Last' mod='pm_advancedsearch4'}|json_encode}
                }
            });
            $(document).on("click", '#dataTable{$id_search|intval} tbody input[type="checkbox"]', function(e) {
                e.stopPropagation();
                e.stopImmediatePropagation();
            });
            $(document).on("click", '#dataTable{$id_search|intval} tr td:gt(0)', function(e) {
                if ($(this).parent("tr").children("td:first-child").find("input:checked").size() > 0) {
                    $(this).parent("tr").children("td:first-child").find("input").prop('checked', false);
                } else {
                    $(this).parent("tr").children("td:first-child").find("input").prop('checked', true);
                }
            });
        });
        </script>
    {/if}
{else}
    {module->_showInfo text="{l s='Please enable Friendly URL in order to use this feature' mod='pm_advancedsearch4'}"}
{/if}