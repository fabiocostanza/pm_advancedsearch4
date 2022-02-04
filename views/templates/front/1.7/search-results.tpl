{extends file='catalog/listing/category.tpl'}

{block name='product_list_header'}
    {if !empty($as_search.keep_category_information) && isset($category) && isset($subcategories)}
        {$smarty.block.parent}
    {else}
        {if $as_seo_description}
            <div class="block-category card card-block hidden-sm-down">
                <h1 class="h1">{$as_seo_title}</h1>
                {if $as_seo_description}
                    <div id="category-description" class="text-muted">{$as_seo_description nofilter}</div>
                {/if}
            </div>
            <div class="text-xs-center hidden-md-up">
                <h1 class="h1">{$as_seo_title}</h1>
            </div>
        {else}
            <h2 class="h2">{$as_seo_title}</h2>
        {/if}
    {/if}
{/block}

{block name='product_list_active_filters'}
    {if !empty($as_search.remind_selection) && ($as_search.remind_selection == 3 || $as_search.remind_selection == 1)}
        <div id="js-active-search-filters" class="hidden-sm-down">
            {$listing.rendered_active_filters nofilter}
        </div>
    {/if}
{/block}

{block name='content'}
    <div id="PM_ASearchResults" data-id-search="{$id_search|intval}">
        <div id="PM_ASearchResultsInner" class="PM_ASearchResultsInner_{$id_search|intval}">
            {$smarty.block.parent}
        </div>
    </div>
{/block}