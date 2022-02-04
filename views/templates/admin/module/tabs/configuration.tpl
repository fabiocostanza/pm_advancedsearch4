{as4_startForm id="formGlobalOptions" iframetarget=false target='_self'}

{if version_compare($smarty.const._PS_VERSION_, '1.7.0.0', '>=')}
	{module->_displaySubTitle text="{l s='General settings' mod='pm_advancedsearch4'}"}
{/if}

{as4_inputActive obj=$config key_active='moduleCache' key_db='moduleCache' label={l s='Enable cache' mod='pm_advancedsearch4'} defaultvalue=$default_config.moduleCache}
{as4_inputActive obj=$config key_active='autoReindex' key_db='autoReindex' label={l s='Activate automatic indexing when adding/editing/deleting items (may slow down your back-office)' mod='pm_advancedsearch4'} defaultvalue=$default_config.autoReindex tips={l s='If you disable this option, you will have to manually reindex the search engine or use cron URL' mod='pm_advancedsearch4'}}
{as4_inputActive obj=$config key_active='autoSyncActiveStatus' key_db='autoSyncActiveStatus' label={l s='Activate the active/inactive status sync of items with your criteria' mod='pm_advancedsearch4'} defaultvalue=$default_config.autoSyncActiveStatus}
{as4_inputActive obj=$config key_active='fullTree' key_db='fullTree' label={l s='Display products from subcategories' mod='pm_advancedsearch4'} defaultvalue=$default_config.fullTree}
{as4_inputActive obj=$config key_active='blurEffect' key_db='blurEffect' label={l s='Enable blur effect when loading results' mod='pm_advancedsearch4'} defaultvalue=$default_config.blurEffect}

{if version_compare($smarty.const._PS_VERSION_, '1.7.0.0', '>=')}
	{module->_displaySubTitle text="{l s='Available sort orders' mod='pm_advancedsearch4'}"}

    {foreach from=$sort_orders item=sort_order}
        {as4_inputActive obj=$config.sortOrders key_active=$sort_order->toString() key_db=$sort_order->toString() label=$sort_order->getLabel() defaultvalue=false}
    {/foreach}
{/if}

{module->_displaySubmit text="{l s='Save' mod='pm_advancedsearch4'}" name='submitModuleConfiguration'}

{as4_endForm id="formGlobalOptions" includehtmlatend=true}