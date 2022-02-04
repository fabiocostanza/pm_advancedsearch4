{if $criterions_groups_indexed|is_array && $criterions_groups_indexed|sizeof}
    {module->_displayTitle text="{l s='Massive add of results pages' mod='pm_advancedsearch4'}"}
    {module->_showInfo text="{l s='Select your criteria among the criterion groups and sort them so they can be readable by human' mod='pm_advancedsearch4'}"}

    {as4_startForm id="seoMassSearchForm"}

    <div id="seoSearchPanelCriteriaTabs" class="massSeo">
        <ul>
            {foreach from=$criterions_groups_indexed key=criterion_group_key item=criterions_group_indexed}
                <li>
                    <a href="#seoSearchPanelCriteriaTabs-{$criterion_group_key|escape:'htmlall':'UTF-8'}">
                        {$criterions_group_indexed.name|escape:'htmlall':'UTF-8'}{if empty($criterions_group_indexed.visible)} {l s='(context)' mod='pm_advancedsearch4'}{/if}
                    </a>
                </li>
            {/foreach}
        </ul>
        {foreach from=$criterions_groups_indexed key=criterion_group_key item=criterions_group_indexed}
            <div id="seoSearchPanelCriteriaTabs-{$criterion_group_key|escape:'htmlall':'UTF-8'}" class="seoSearchPanelCriteriaTabsContent">
                <input type="hidden" name="id_criterion_group" value="{$criterions_group_indexed.id_criterion_group|intval}" style="margin-left:4px;" />
                {as4_button text={l s='Check/uncheck all' mod='pm_advancedsearch4'} onclick="enableAllCriterion4MassSeo(this);" icon_class='ui-icon ui-icon-check'}
                <br /><br />
                <ul class="ui-helper-reset ui-sortable">
                {* Range group *}
                {if !empty($criterions_group_indexed.range) && $criterions_group_indexed.display_type != 5 && $criterions_group_indexed.display_type != 8}
                    {foreach from=$criterions_group_indexed.criterions item=criterion}
                        <li class="ui-state-default massSeoSearchCriterion" id="criterion_{$criterions_group_indexed.id_criterion_group|intval}_{$criterion.id_criterion|as4_nofilter}" title="{$criterion.value|escape:'htmlall':'UTF-8'}" onclick="enableCriterion4MassSeo(this);">
                            <input type="checkbox" name="criteria[{$criterions_group_indexed.id_criterion_group|intval}][]" value="{$criterions_group_indexed.id_criterion_group|intval}_{$criterion.id_criterion|as4_nofilter}" onclick="enableCriterion4MassSeo($(this).parent('li'));" /> &nbsp; {$criterion.value|escape:'htmlall':'UTF-8'}
                        </li>
                    {/foreach}
                {* Price group *}
                {elseif $criterions_group_indexed.criterion_group_type == 'price'}
                    <li 
                    class="ui-state-default massSeoSearchCriterion massSeoSearchCriterionPrice" 
                    id="criterion_price-{$criterions_group_indexed.price_range.min_price|floatval}~{$criterions_group_indexed.price_range.max_price|floatval}" 
                    title="{l s='From' mod='pm_advancedsearch4'} {$criterions_group_indexed.price_range.min_price|intval} {l s='to' mod='pm_advancedsearch4'} {$default_currency_sign_left|escape:'htmlall':'UTF-8'} {$criterions_group_indexed.price_range.max_price|intval} {$default_currency_sign_right|escape:'htmlall':'UTF-8'}" 
                    onclick="enableCriterion4MassSeo(this);" 
                    style="height:50px"
                    >
                        <input type="checkbox" id="massSeoSearchCriterionPriceInput" name="criteria[{$criterions_group_indexed.id_criterion_group|intval}][]" value="{$criterions_group_indexed.id_criterion_group|intval}_{if isset($criterions_group_indexed.price_range.min_price)}{$criterions_group_indexed.price_range.min_price|floatval}~{$criterions_group_indexed.price_range.max_price|floatval}{/if}" onclick="enableCriterion4MassSeo($(this).parent('li'));" /> &nbsp; 
                        {l s='Define price range:' mod='pm_advancedsearch4'}<br /><br />
                        <div id="PM_ASSeoPriceRange"></div>
                        <span id="PM_ASPriceRangeValue">
                            {$criterions_group_indexed.price_range.min_price|intval} - {$default_currency_sign_left|escape:'htmlall':'UTF-8'} {$criterions_group_indexed.price_range.max_price|intval} {$default_currency_sign_right|escape:'htmlall':'UTF-8'}
                        </span>
                        <select id="id_currency" name="id_currency" style="float:left;margin-left:10px;width:50px;{if sizeof($currencies) == 1}display:none;{/if}">
                        {foreach from=$currencies item=currency}
                            <option value="{$currency.id_currency|intval}"{if $default_currency_id == $currency.id_currency} selected="selected"{/if}>{$currency.sign|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                        </select>

                        <script type="text/javascript">
                            $(document).on('change', '#id_currency', function() {
                                var id_currency = $(this).val();
                                $.ajax({
                                    type : "GET",
                                    url : "{$base_config_url|as4_nofilter}&pm_load_function=displaySeoPriceSlider&id_search={$criterions_group_indexed.id_search|intval}&id_criterion_group_linked={$criterions_group_indexed.id_criterion_group_linked|intval}&id_criterion_group={$criterions_group_indexed.id_criterion_group|intval}&id_currency=" + id_currency,
                                    dataType : "script"
                                });
                            });
                            $("#PM_ASSeoPriceRange").slider({
                                range: true,
                                min: {$criterions_group_indexed.price_range.min_price|intval},
                                max: {$criterions_group_indexed.price_range.max_price|intval},
                                values: [ {$criterions_group_indexed.price_range.min_price|intval}, {$criterions_group_indexed.price_range.max_price|intval} ],
                                slide: function(event, ui) {
                                    $("#PM_ASPriceRangeValue").html(ui.values[0] + " - " + "{$default_currency_sign_left|escape:'htmlall':'UTF-8'}" + ui.values[1] + "{$default_currency_sign_right|escape:'htmlall':'UTF-8'}");
                                    $(".seoSearchCriterionPriceSortable").attr("id", "criterion_{$criterions_group_indexed.id_criterion_group|intval}_" + ui.values[0] + "~" + ui.values[1]);
                                    $(".seoSearchCriterionPriceSortable").attr("title", "{l s='From' mod='pm_advancedsearch4'} " + ui.values[0] + " {l s='to' mod='pm_advancedsearch4'} " + "{$default_currency_sign_left|escape:'htmlall':'UTF-8'}" + ui.values[1] + "{$default_currency_sign_right|escape:'htmlall':'UTF-8'}");
                                }
                            });
                        </script>
                    </li>
                {* Slider group *}
                {elseif $criterions_group_indexed.display_type == 5 || $criterions_group_indexed.display_type == 8}
                    <li 
                    class="ui-state-default massSeoSearchCriterion seoSearchCriterionRangeSortable{$criterions_group_indexed.id_criterion_group|intval}" 
                    id="criterion_{$criterions_group_indexed.id_criterion_group|intval}_{$criterions_group_indexed.range.min|floatval}~{$criterions_group_indexed.range.max|floatval}" 
                    title="{l s='From' mod='pm_advancedsearch4'} {$criterions_group_indexed.range.min|intval} {l s='to' mod='pm_advancedsearch4'} {$criterions_group_indexed.range.max|intval}  ({$criterions_group_indexed.range_sign|escape:'htmlall':'UTF-8'})" 
                    onclick="enableCriterion4MassSeo(this);"
                    style="height:50px"
                    >
                        <input type="checkbox" id="massSeoSearchCriterionRangeInput{$criterions_group_indexed.id_criterion_group|intval}" name="criteria[{$criterions_group_indexed.id_criterion_group|intval}][]" value="{$criterions_group_indexed.id_criterion_group|intval}_{$criterions_group_indexed.id_criterion_group|intval}_{$criterions_group_indexed.range.min|floatval}~{$criterions_group_indexed.range.max|floatval}" onclick="enableCriterion4MassSeo($( this ).parent('li'));" /> &nbsp; 
                        {l s='Define range:' mod='pm_advancedsearch4'}<br />
                        <div id="PM_ASSeoRange{$criterions_group_indexed.id_criterion_group|intval}" style="width:30%;margin-left:10px;float:left"></div>
                        <span id="PM_ASRangeValue{$criterions_group_indexed.id_criterion_group|intval}" style="width:35%;display:block;float:left;font-size:11px;margin-left:10px;">{$criterions_group_indexed.range.min|floatval} - {$criterions_group_indexed.range.max|floatval} ({$criterions_group_indexed.range_sign|escape:'htmlall':'UTF-8'})</span>
                        <script type="text/javascript">
                            $("#PM_ASSeoRange{$criterions_group_indexed.id_criterion_group|intval}").slider({
                                range: true,
                                min: {$criterions_group_indexed.range.min|intval},
                                max: {$criterions_group_indexed.range.max|intval},
                                values: [ {$criterions_group_indexed.range.min|floatval}, {$criterions_group_indexed.range.max|floatval} ],
                                slide: function(event, ui) {
                                    $("#PM_ASRangeValue{$criterions_group_indexed.id_criterion_group|intval}" ).html(ui.values[0] + " - " + ui.values[1]  + " ({$criterions_group_indexed.range_sign|escape:'htmlall':'UTF-8'})");
                                    $("#massSeoSearchCriterionRangeInput{$criterions_group_indexed.id_criterion_group|intval}").val('{$criterions_group_indexed.id_criterion_group|intval}' + "_" + ui.values[0] + "~" + ui.values[1]);
                                    $(".seoSearchCriterionRangeSortable{$criterions_group_indexed.id_criterion_group|intval}" ).attr("id", "criterion_{$criterions_group_indexed.id_criterion_group|intval}_" + ui.values[0] + "~" + ui.values[1]);
                                    $(".seoSearchCriterionRangeSortable{$criterions_group_indexed.id_criterion_group|intval}" ).attr("title", "{l s='From' mod='pm_advancedsearch4'} " + ui.values[0] + " {l s='to' mod='pm_advancedsearch4'} " + ui.values[1] + " ({$criterions_group_indexed.range_sign|escape:'htmlall':'UTF-8'})");
                                }
                            });
                    </script>
                    </li>
                {* Classic group *}
                {else}
                    {foreach from=$criterions_group_indexed.criterions item=criterion}
                        <li class="ui-state-default massSeoSearchCriterion" id="criterion_{$criterion.id_criterion|as4_nofilter}" title="{$criterion.value|escape:'htmlall':'UTF-8'}" onclick="enableCriterion4MassSeo(this);">
                            <input type="checkbox" name="criteria[{$criterions_group_indexed.id_criterion_group|intval}][]" value="{$criterions_group_indexed.id_criterion_group|intval}_{$criterion.id_criterion|as4_nofilter}" onclick="enableCriterion4MassSeo($(this).parent('li'));" /> &nbsp; 
                            {$criterion.value|escape:'htmlall':'UTF-8'}
                        </li>
                    {/foreach}
                {/if}
                </ul>
            </div>
        {/foreach}
    </div>
    <div id="seoMassSearchPanelCriteriaGroupsTabs">
        <ul style="width:835px;min-height:50px;" class="ui-helper-reset ui-sortable">
        {foreach from=$criterions_groups_indexed key=criterion_group_key item=criterions_group_indexed}
            <li class="ui-state-default seoSearchCriterionSortable ui-state-disabled seoSearchCriterionGroupSortable" id="criterion_group_{$criterions_group_indexed.id_criterion_group|intval}" style="display:none;">
                <a href="#seoSearchPanelCriteriaTabs-{$criterion_group_key|escape:'htmlall':'UTF-8'}">
                    {$criterions_group_indexed.name|escape:'htmlall':'UTF-8'}{if empty($criterions_group_indexed.visible)} {l s='(context)' mod='pm_advancedsearch4'}{/if}
                </a>
            </li>
        {/foreach}
            </ul>
        </div>
        <input type="hidden" name="id_search" id="id_search" value="{$id_search|intval}" />
        <input type="hidden" name="criteria_groups" id="massSeoSearchCriterionGroupsInput" />
    </div>

    {include file='../../core/clear.tpl'}

    <script type="text/javascript">
        var msgMaxCriteriaForMass = {{l s='You can not select more than three groups of criteria' mod='pm_advancedsearch4'}|json_encode};
        var msgNoSeoCriterion = {{l s='You must choose at least one criterion to use this option' mod='pm_advancedsearch4'}|json_encode};
        {literal}
        $(document).ready(function() {
            $('select#id_currency').click(function(e) {
                e.stopPropagation();
            });
            $("#seoSearchPanelCriteriaTabs").tabs({cache:false});
            $("#seoMassSearchPanelCriteriaGroupsTabs ul").sortable({
                items: "li:not(.ui-state-disabled)",
                placeholder: "ui-state-highlight seoSearchCriterionSortable",
                sort: function() {
                    $(this).removeClass("ui-state-default");
                },
                update: function(event, ui) {
                    massSeoSearchCriteriaGroupUpdate();
                }
            });
        });
        {/literal}
    </script>

    {as4_inputActive obj=false key_active='massSeoSearchCrossLinks' key_db='massSeoSearchCrossLinks' label={l s='Automatically add cross links between the generated pages' mod='pm_advancedsearch4'} defaultvalue=true}

    {module->_displaySubmit text="{l s='Generate pages' mod='pm_advancedsearch4'}" name="submitMassSeoSearchForm"}

    {include file='../../core/clear.tpl'}

    {as4_endForm id="seoMassSearchForm"}
{else}
    {module->_showInfo text="{l s='Before adding a new page, please add criteria groups to your search engine' mod='pm_advancedsearch4'}"}
{/if}