<script type="text/javascript">
    var criteriaGroupToReindex = {$groups_to_reindex|json_encode};
</script>

<div class="width3">
    <label>{l s='Clear search engines cache' mod='pm_advancedsearch4'}</label>
    <div class="margin-form">
        <a href="{$base_config_url|as4_nofilter}&pm_load_function=processClearAllCache" class="ajax_script_load ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" style="padding:7px 5px;">
            {l s='Clear cache' mod='pm_advancedsearch4'}
        </a>
        <div class="clear"></div>
    </div>
</div>

<div class="width3">
    <label>{l s='Reindex all search engines (CRON alternative)' mod='pm_advancedsearch4'}</label>
    <div class="margin-form">
        <a id="reindexAllSearchLink" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" style="padding:7px 5px;">
            {l s='Reindex all' mod='pm_advancedsearch4'}
        </a>
        <div class="progressbar_wrapper progressbarReindexSpecificSearch">
            <div class="progressbar" id="progressbarReindexAllSearch"></div>
            <div class="progressbarpercent"></div>
        </div>
        <div class="clear"></div>
    </div>
</div>

<div class="width3">
    <label>{l s='Delete all search engines' mod='pm_advancedsearch4'}</label>
    <div class="margin-form">
        <a href="{$base_config_url|as4_nofilter}&pm_load_function=processClearAllTables" title="{l s='This will delete all your search engines! Are you really sure?' mod='pm_advancedsearch4'}" class="ajax_script_load ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only pm_confirm" style="padding:7px 5px;">
            {l s='Remove all' mod='pm_advancedsearch4'}
        </a>
        <div class="clear"></div>
    </div>
</div>

<script type="text/javascript">
    $("#reindexAllSearchLink").unbind("click").bind("click",function() {
        reindexSearchCriterionGroups($("#reindexAllSearchLink"), criteriaGroupToReindex, "#progressbarReindexAllSearch");
    });
</script>