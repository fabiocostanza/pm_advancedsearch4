{if $criterions_groups_indexed|is_array && $criterions_groups_indexed|sizeof}
    {if !empty($currentSeo->id)}
        {module->_displayTitle text="{l s='Edit results page' mod='pm_advancedsearch4'}"}
    {else}
        {module->_displayTitle text="{l s='Add results page' mod='pm_advancedsearch4'}"}
    {/if}
    {as4_startForm id="seoSearchForm" obj=$params.obj params=$params}

    <div id="seoSearchPanelCriteriaTabs">
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
                <ul class="ui-helper-reset ui-sortable">
                {* Range group *}
                {if !empty($criterions_group_indexed.range) && $criterions_group_indexed.display_type != 5 && $criterions_group_indexed.display_type != 8}
                    {foreach from=$criterions_group_indexed.criterions item=criterion}
                        <li class="ui-state-default seoSearchCriterionSortable" id="criterion_{$criterions_group_indexed.id_criterion_group|intval}_{$criterion.id_criterion|as4_nofilter}" title="{$criterion.value|escape:'htmlall':'UTF-8'}">
                            {$criterion.value|escape:'htmlall':'UTF-8'}
                        </li>
                    {/foreach}
                {* Price group *}
                {elseif $criterions_group_indexed.criterion_group_type == 'price'}
                    <li 
                    class="ui-state-default seoSearchCriterionSortable seoSearchCriterionPriceSortable" 
                    id="criterion_{$criterions_group_indexed.id_criterion_group|intval}_{$criterions_group_indexed.price_range.min_price|floatval}~{$criterions_group_indexed.price_range.max_price|floatval}" 
                    title="{l s='From' mod='pm_advancedsearch4'} {$criterions_group_indexed.price_range.min_price|intval} {l s='to' mod='pm_advancedsearch4'} {$default_currency_sign_left|escape:'htmlall':'UTF-8'} {$criterions_group_indexed.price_range.max_price|intval} {$default_currency_sign_right|escape:'htmlall':'UTF-8'}" 
                    style="width:50%"
                    >
                        {l s='Define price range:' mod='pm_advancedsearch4'}<br />
                        <div id="PM_ASSeoPriceRange"></div>
                        <span id="PM_ASPriceRangeValue">
                            {$criterions_group_indexed.price_range.min_price|intval} - {$default_currency_sign_left|escape:'htmlall':'UTF-8'} {$criterions_group_indexed.price_range.max_price|intval} {$default_currency_sign_right|escape:'htmlall':'UTF-8'}
                        </span>
                        <select id="id_currency" name="id_currency" style="float:left;margin-left:10px;width:50px;">
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
                    class="ui-state-default seoSearchCriterionSortable seoSearchCriterionRangeSortable{$criterions_group_indexed.id_criterion_group|intval}" 
                    id="criterion_{$criterions_group_indexed.id_criterion_group|intval}_{$criterions_group_indexed.range.min|floatval}~{$criterions_group_indexed.range.max|floatval}" 
                    title="{l s='From' mod='pm_advancedsearch4'} {$criterions_group_indexed.range.min|intval} {l s='to' mod='pm_advancedsearch4'} {$criterions_group_indexed.range.max|intval} ({$criterions_group_indexed.range_sign|escape:'htmlall':'UTF-8'})" 
                    style="width:50%"
                    >
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
                                    $("#PM_ASRangeValue{$criterions_group_indexed.id_criterion_group|intval}").html(ui.values[0] + " - " + ui.values[1]  + " ({$criterions_group_indexed.range_sign|escape:'htmlall':'UTF-8'})");
                                    $(".seoSearchCriterionRangeSortable{$criterions_group_indexed.id_criterion_group|intval}" ).attr("id", "criterion_{$criterions_group_indexed.id_criterion_group|intval}_" + ui.values[0] + "~" + ui.values[1]);
                                    $(".seoSearchCriterionRangeSortable{$criterions_group_indexed.id_criterion_group|intval}" ).attr("title", "{l s='From' mod='pm_advancedsearch4'} " + ui.values[0] + " {l s='to' mod='pm_advancedsearch4'} " + ui.values[1] + " ({$criterions_group_indexed.range_sign|escape:'htmlall':'UTF-8'})");
                                }
                            });
                    </script>
                    </li>
                {* Classic group *}
                {else}
                    {foreach from=$criterions_group_indexed.criterions item=criterion}
                    <li class="ui-state-default seoSearchCriterionSortable" id="criterion_{$criterions_group_indexed.id_criterion_group|intval}_{$criterion.id_criterion|as4_nofilter}" title="{$criterion.value|escape:'htmlall':'UTF-8'}">
                        {$criterion.value|escape:'htmlall':'UTF-8'}
                    </li>
                    {/foreach}
                {/if}
                </ul>
            </div>
        {/foreach}
    </div>
    {module->_showInfo text="{l s='Add the criteria of your choice using drag & drop to generate predefined searches.' mod='pm_advancedsearch4'}<br /><br />{l s='You can sort them and automaticaly generate friendly title, meta and URL.' mod='pm_advancedsearch4'}"}
    <div id="nbProductsCombinationSeoSearchForm"></div>
    <div id="seoSearchPanelCriteriaSelected">
        <div class="ui-widget-content" style="padding:10px;">
            <ul style="width:835px;float:left;min-height:50px;" class="ui-helper-reset ui-sortable">
                {if $criteria|is_array && $criteria|sizeof}
                    {foreach from=$criteria item=criterion}
                        <li class="ui-state-default seoSearchCriterionSortable" id="biscriterion_{$criterion|as4_nofilter}" rel="criterion_{$criterion|as4_nofilter}">
                            <span class="ui-icon ui-icon-close" style="float: left; margin-right: .3em;cursor:pointer;" onclick="removeSelectedSeoCriterion(this);"></span> {$criteria_values[$criterion]|escape:'htmlall':'UTF-8'}
                            <script type="text/javascript">
                                $('[id="criterion_{$criterion|as4_nofilter}"]').hide();
                            </script>
                        </li>
                    {/foreach}
                {else}
                    <li class="placeholder">{l s='Drop your criteria here' mod='pm_advancedsearch4'}</li>
                {/if}
            </ul>
            {include file='../../core/clear.tpl'}
        </div>
    </div>

    <script type="text/javascript">
        var msgNoSeoCriterion = {{l s='You must choose at least one criteria to use this option' mod='pm_advancedsearch4'}|json_encode};
        $(document).ready(function() {
            $("#seoSearchPanelCriteriaTabs").tabs({ cache:false });
            $(".seoSearchPanelCriteriaTabsContent li").draggable({
                appendTo: "body",
                helper: "clone"
            });
            $("#seoSearchPanelCriteriaSelected ul").droppable({
                activeClass: "ui-state-default",
                hoverClass: "ui-state-hover",
                accept: ":not(.ui-sortable-helper)",
                drop: function( event, ui) {
                    $(this).find(".placeholder").remove();
                    if (ui.draggable.hasClass("seoSearchCriterionPriceSortable")) {
                        $('<li class="ui-state-default seoSearchCriterionSortable" id="bis' + ui.draggable.attr("id") + '" rel="' + ui.draggable.attr("id") + '"></li>').html('<span class="ui-icon ui-icon-close" style="float: left; margin-right: .3em;cursor:pointer;" onclick="removeSelectedSeoCriterion(this);"></span> ' + ui.draggable.attr("title")).appendTo(this);
                    } else {
                        $('<li class="ui-state-default seoSearchCriterionSortable" id="bis' + ui.draggable.attr("id") + '" rel="' + ui.draggable.attr("id") + '"></li>').html('<span class="ui-icon ui-icon-close" style="float: left; margin-right: .3em;cursor:pointer;" onclick="removeSelectedSeoCriterion(this);"></span> ' + ui.draggable.attr("title")).appendTo(this);
                    }
                    ui.draggable.fadeOut("fast");
                    seoSearchCriteriaUpdate();
                }
            }).sortable({
                items: "li:not(.placeholder)",
                placeholder: "ui-state-highlight seoSearchCriterionSortable",
                sort: function() {
                    $(this).removeClass("ui-state-default");
                },
                update: function(event, ui) {
                    seoSearchCriteriaUpdate();
                }
            });
            seoSearchCriteriaUpdate();
        });
    </script>

    {as4_button text={l s='Generate title, meta and URL' mod='pm_advancedsearch4'} onclick='fillSeoFields();' icon_class='ui-icon ui-icon-refresh'}
    <br /><br />
    
    {include file='../../core/clear.tpl'}
    {as4_inputTextLang obj=$params.obj key='meta_title' label={l s='Meta title' mod='pm_advancedsearch4'} size='350px'}
    {as4_inputTextLang obj=$params.obj key='meta_description' label={l s='Meta description' mod='pm_advancedsearch4'} size='350px'}
    {as4_inputTextLang obj=$params.obj key='meta_keywords' label={l s='Meta keywords' mod='pm_advancedsearch4'} size='350px'}
    {as4_inputTextLang obj=$params.obj key='title' label={l s='Title (H1)' mod='pm_advancedsearch4'} size='350px'}
    {as4_inputTextLang obj=$params.obj key='seo_url' label={l s='Friendly URL' mod='pm_advancedsearch4'} size='350px' onkeyup='ASStr2url(this);' onchange='ASStr2url(this);'}
    {as4_richTextareaLang obj=$params.obj key='description' label={l s='Description (visible on the top of the page)' mod='pm_advancedsearch4'}}
    {as4_ajaxSelectMultiple selectedoptions=$cross_links_selected key='cross_links' label={l s='Results pages to link to this page (cross-linking)' mod='pm_advancedsearch4'} remoteurl="{$base_config_url|as4_nofilter}&pm_load_function=displaySeoSearchOptions&id_seo_origin={$params.obj->id}"}

    <input type="hidden" name="criteria" id="seoSearchCriteriaInput" />
    <input type="hidden" name="id_search" id="id_search" value="{$id_search|intval}" />
    {if !empty($currentSeo->id)}
        <input type="hidden" name="id_seo" value="{$currentSeo->id|intval}" />
        <input type="hidden" name="id_currency" id="posted_id_currency" value="{$currentSeo->id_currency|intval}" />
        <script type="text/javascript">
            var id_currency = {$currentSeo->id_currency|json_encode};
        </script>
    {else}
        <input type="hidden" name="id_currency" id="posted_id_currency" />
    {/if}
    <br class="clear" /><br />

    <center>
        <p class="ui-state-error ui-corner-all" id="errorCombinationSeoSearchForm" style="display:none;padding:10px;">
            <strong>{l s='Your criteria combination led to no result, please reorder them before submiting' mod='pm_advancedsearch4'}</strong>
        </p>
    </center>

    {module->_displaySubmit text="{l s='Save' mod='pm_advancedsearch4'}" name="submitSeoSearchForm"}
    {as4_endForm id="seoSearchForm"}
{else}
    {module->_showInfo text="{l s='Before adding a new page, please add criteria groups to your search engine'}"}
{/if}