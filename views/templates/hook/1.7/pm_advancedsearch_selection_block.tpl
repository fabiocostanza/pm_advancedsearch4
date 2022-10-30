{if empty($selectionFromGroups)}<section id="js-active-search-filters">{/if}
    {if !empty($as_search.hasOneVisibleSelectedCriterion)}
    <form action="{$ASSearchUrlForm}" method="POST" class="PM_ASSelectionsBlock PM_ASSelections active_filters" data-id-search="{$as_search.id_search|intval}">
        {if is_array($as_search.selected_criterion) && sizeof($as_search.selected_criterion)}
            {assign var='current_selection' value=$as_search.selected_criterion}
        {else}
            {assign var='current_selection' value=array()}
        {/if}

        {if !empty($as_search.hasOneVisibleSelectedCriterion)}
            <div class="h6 active-filter-title">{l s='Active filters' mod='pm_advancedsearch4'}</div>
            <ul>
                {foreach from=$as_search.criterions_groups_selected item=criterions_group name=criterions_groups}
                    {if isset($as_search.criterions[$criterions_group.id_criterion_group]) && $criterions_group.visible && isset($current_selection[$criterions_group.id_criterion_group]) && is_array($current_selection[$criterions_group.id_criterion_group]) && sizeof($current_selection[$criterions_group.id_criterion_group])}
                        {foreach from=$as_search.criterions_selected[$criterions_group.id_criterion_group] key=criterion_key item=criterion name=criterions}
                            {if !empty($criterion.visible) && (isset($criterion.id_criterion) AND isset($as_search.selected_criterion[$criterions_group.id_criterion_group]) AND is_array($as_search.selected_criterion[$criterions_group.id_criterion_group]) AND $criterion.id_criterion|in_array:$as_search.selected_criterion[$criterions_group.id_criterion_group]) || isset($criterion.min)}
                                <li>
                                    <a href="#" class="PM_ASSelectionsRemoveLink filter-block">{$criterions_group.name} : {if isset($criterion.min) && isset($criterion.max)}{$criterion.min|floatval} {$criterion.max|floatval}{else}{$criterion.value}{/if}</a>
                                    <input type="hidden" name="as4c[{$criterions_group.id_criterion_group|intval}][]" value="{$criterion.id_criterion nofilter}" />
                                </li>
                            {/if}
                        {/foreach}
                        </li>
                    {/if}
                {/foreach}
            </ul>
        {/if}

        {* Hidden criterions *}
        {foreach from=$as_search.criterions_groups item=criterions_group name=criterions_groups}
            {if isset($as_search.selected_criterion[$criterions_group.id_criterion_group])}
                {foreach from=$as_search.selected_criterion[$criterions_group.id_criterion_group] item=selected_id_criterion name=selected_criteria}
                    {if !$criterions_group.visible}
                        <input type="hidden" name="as4c[{$criterions_group.id_criterion_group|intval}][]" value="{$selected_id_criterion nofilter}" />
                        <input type="hidden" name="as4c_hidden[{$criterions_group.id_criterion_group|intval}][]" value="{$selected_id_criterion nofilter}" />
                    {/if}
                {/foreach}
            {/if}
        {/foreach}
        <input type="hidden" name="id_search" value="{$as_search.id_search|intval}" />
        {if \AdvancedSearch\SearchEngineUtils::getCurrentCategory()}
            <input type="hidden" name="id_category_search" value="{if isset($as_search.id_category_root) && $as_search.id_category_root > 0}{$as_search.id_category_root|intval}{else if \AdvancedSearch\SearchEngineUtils::getCurrentCategory()}{\AdvancedSearch\SearchEngineUtils::getCurrentCategory()|intval}{/if}" />
        {/if}
        {if \AdvancedSearch\SearchEngineUtils::getCurrentManufacturer()}
            <input type="hidden" name="id_manufacturer_search" value="{\AdvancedSearch\SearchEngineUtils::getCurrentManufacturer()|intval}" />
        {/if}
        {if \AdvancedSearch\SearchEngineUtils::getCurrentSupplier()}
            <input type="hidden" name="id_supplier_search" value="{\AdvancedSearch\SearchEngineUtils::getCurrentSupplier()|intval}" />
        {/if}
        {if \AdvancedSearch\SearchEngineUtils::getCurrentCMS()}
            <input type="hidden" name="id_cms_search" value="{\AdvancedSearch\SearchEngineUtils::getCurrentCMS()|intval}" />
        {/if}
        {if isset($smarty.get.id_seo)}
            <input type="hidden" name="id_seo" value="{$smarty.get.id_seo|intval}" />
        {/if}

    </form>
    {/if}
{if empty($selectionFromGroups)}</section><!-- #js-active-search-filters -->{/if}
