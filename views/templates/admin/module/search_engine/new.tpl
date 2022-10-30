{as4_startForm id='searchForm' obj=$params.obj params=$params}
{if !empty($params.obj->id)}
    {module->displayTitle text="{l s='Edit search engine' mod='pm_advancedsearch4'}"}
{else}
    {module->displayTitle text="{l s='Add a new search engine' mod='pm_advancedsearch4'}"}
{/if}

{* General settings *}
{as4_startFieldset title={l s='General settings' mod='pm_advancedsearch4'} hide=false}
    {* Type of search *}
    {as4_inputText obj=$params.obj key='internal_name' label={l s='Internal title' mod='pm_advancedsearch4'}}
    {as4_inputTextLang obj=$params.obj key='title' label={l s='Public title' mod='pm_advancedsearch4'}}
    <h4>{l s='Behaviour' mod='pm_advancedsearch4'}</h4>
    {as4_select obj=$params.obj options=$searchType label={l s='Type of search' mod='pm_advancedsearch4'} key='search_type' size='300px' onchange='toggleSearchEngineSettings(true);'}
    <input type="hidden" name="step_search" value="{$params.obj->step_search|intval}" />
    {* Use context or not *}
    {as4_select obj=$params.obj options=$where_to_search label={l s='Where to search ?' mod='pm_advancedsearch4'} key='filter_by_emplacement' size='400px'}
    <div class="id_category_root_container">
        {as4_select obj=$params.obj options=$category_select label={l s='Starting category for the search (default is user-context category)' mod='pm_advancedsearch4'} key='id_category_root' size='500px'}
    </div>
    {as4_inputActive obj=$params.obj key_active='recursing_indexing' key_db='recursing_indexing' label={l s='Search through all sub-categories' mod='pm_advancedsearch4'}}
    {as4_inputActive obj=$params.obj key_active='search_on_stock' key_db='search_on_stock' label={l s='Search only among products in stock (with reduced performance)' mod='pm_advancedsearch4'}}
    <div class="search_method_options_2"{if $params.obj->search_method != 1} style="display:none"{/if}>
        {as4_inputActive obj=$params.obj key_active='redirect_one_product' key_db='redirect_one_product' label={l s='Straight redirect to product if only one result is found' mod='pm_advancedsearch4'} defaultvalue=true}
    </div>
{as4_endFieldset}
{* /General settings *}

{* Search block settings *}
{as4_startFieldset title={l s='Search block settings' mod='pm_advancedsearch4'} hide=false}
    {* Location *}
    {as4_select obj=$params.obj options=$hooksList label={l s='Position' mod='pm_advancedsearch4'} defaultvalue={l s='Do not show' mod='pm_advancedsearch4'} key='id_hook' size='300px' onchange="updateHookOptions($(this), {$hooksId|json_encode});"}
    {if $seo_searchs|is_array && $seo_searchs|sizeof}
        {module->showInfo text="
            {l s='SEO Pages:' mod='pm_advancedsearch4'}<br />
            {l s='Be sure to have the current selected hook/location shown on “module-%s-seo“ pages.' mod='pm_advancedsearch4' sprintf='pm_advancedsearch4'}<br />
            <a href=\"{$theme_layout_preferences_link|escape:'htmlall':'UTF-8'}\" target=\"_blank\">{l s='For left/right column hook, you may check you theme preferences in order to enable the choosen column for this page.' mod='pm_advancedsearch4'}</a><br />
            {l s='For any other location, please be sure to include the right code to seo.tpl file when needed.' mod='pm_advancedsearch4'}<br />
        "}
    {/if}

    <div class="hookOptions hookOption-1" style="display:none;">
        <input type="hidden" name="smarty_var_name" value="{$params.obj->smarty_var_name|as4_nofilter}" />
        <input type="hidden" name="insert_in_center_column" value="{$params.obj->insert_in_center_column|intval}" />
        {if Validate::isLoadedObject($params.obj)}
            {module->showInfo text="
                <p>{l s='Please insert the following code in your template at the line where you want the search engine to appear:' mod='pm_advancedsearch4'}</p>
                <div id=\"custom_content_results\" onclick=\"selectText('smarty_var_name_picker')\" data-no-smarty-var=\"true\">
                    <pre id=\"smarty_var_name_picker\">{ldelim}widget name=\"pm_advancedsearch4\" id_search_engine=\"{$params.obj->id|intval}\"{rdelim}</pre>
                </div>
                <p><strong>{l s='Please be sure to have the previous widget code added into views/templates/front/1.7/search-results.tpl if you want to get the search engine shown on search results pages.' mod='pm_advancedsearch4'}</strong></p>
            "}
        {else}
            {module->showInfo text="{l s='The widget code is provided once the search engine is saved.' mod='pm_advancedsearch4'}"}
        {/if}
    </div>

    <div class="hookOptions hookOption-2">
        {as4_select obj=$params.obj options=$widgetHooksList label={l s='Hook to use' mod='pm_advancedsearch4'} key='id_hook_widget' size='250px'}
        {module->showWarning text="{l s='This module must be transplanted to the hook you will choose above (you can do this using “Manage hooks“ link)' mod='pm_advancedsearch4'}"}
    </div>

    <div class="hookOptions hookOption-{'displaytop'|array_search:$hooksId|intval} hookOption-{'displayhome'|array_search:$hooksId|intval} hookOption-{'displaynavfullwidth'|array_search:$hooksId|intval} hookOption-1 hookOption-2" style="display:none;">
        {as4_inputText obj=$params.obj key='css_classes' label={l s='CSS classes to apply' mod='pm_advancedsearch4'}}
    </div>
    {* /Location *}

    {as4_select obj=$params.obj options=$options_search_method label={l s='Search submission method' mod='pm_advancedsearch4'} key='search_method' onchange='display_search_method_options();' size='300px'}

    {as4_inputActive obj=$params.obj key_active='display_nb_result_on_blc' key_db='display_nb_result_on_blc' label={l s='Display number of products found on search block' mod='pm_advancedsearch4'}}
    {as4_inputActive obj=$params.obj key_active='remind_selection_block' key_db='remind_selection_block' label={l s='Display a reminder of the selected criteria on search block' mod='pm_advancedsearch4'}}

    {as4_inputActive obj=$params.obj key_active='scrolltop_active' key_db='scrolltop_active' label={l s='Enable auto scrolling effect' mod='pm_advancedsearch4'} defaultvalue=true}
    {as4_inputActive obj=$params.obj key_active='unique_search' key_db='unique_search' label={l s='Primary' mod='pm_advancedsearch4'} tips={l s='When enabled, only this search engine will be displayed on your page. If other search engines have this option enabled, they\'ll be displayed too' mod='pm_advancedsearch4'}}

    <h4>{l s='Criteria groups' mod='pm_advancedsearch4'}</h4>
    {as4_inputActive obj=$params.obj key_active='reset_group' key_db='reset_group' label={l s='Display a link to reset selection' mod='pm_advancedsearch4'} defaultvalue=true}
    <div class="enabled-option-step-search">
        {as4_inputActive obj=$params.obj key_active='step_search_next_in_disabled' key_db='step_search_next_in_disabled' label={l s='Display next criterion groups in disabled mode' mod='pm_advancedsearch4'}}
    </div>

    <div class="hide-empty-criterion-group">
        {as4_inputActive obj=$params.obj key_active='hide_empty_crit_group' key_db='hide_empty_crit_group' label={l s='Hide empty criteria groups' mod='pm_advancedsearch4'}}
    </div>
    {as4_inputActive obj=$params.obj key_active='hide_criterions_group_with_no_effect' key_db='hide_criterions_group_with_no_effect' label={l s='Hide implicit criteria groups' mod='pm_advancedsearch4'} defaultvalue=false}

    <h4>{l s='Criteria' mod='pm_advancedsearch4'}</h4>
    {as4_inputActive obj=$params.obj key_active='display_nb_result_criterion' key_db='display_nb_result_criterion' label={l s='Display product count per criteria' mod='pm_advancedsearch4'}}
    {as4_inputActive obj=$params.obj key_active='display_empty_criteria' key_db='display_empty_criteria' label={l s='Display empty criteria' mod='pm_advancedsearch4'} defaultvalue=false onclick='toggleSearchEngineSettings(false)'}
    {as4_select obj=$params.obj options=$options_hide_criterion_method label={l s='Out-of-bounds criteria display method (limit to be defined in group settings)' mod='pm_advancedsearch4'} key='show_hide_crit_method'}
{as4_endFieldset}
{* /General settings *}

{* Search results settings *}
{as4_startFieldset title={l s='Search results settings' mod='pm_advancedsearch4'} hide=false}
    <div id="blc_search_results_selector" data-default-selector="{$default_search_results_selector|as4_nofilter}" style="{if !empty($params.obj->id_hook) && ($params.obj->id_hook == 8 || !empty($params.obj->insert_in_center_column))}display:none{/if}">
        {as4_inputText obj=$params.obj key='search_results_selector' label={l s='CSS selector of the center column' mod='pm_advancedsearch4'} defaultvalue=$default_search_results_selector}
    </div>
    {as4_inputText obj=$params.obj key='search_results_selector_css' label={l s='CSS classes to add to the center column' mod='pm_advancedsearch4'}}
    {if $spa_module_is_active}
        <div class="displayPriorityOnCombinationImage" style="display: none;">
    {/if}
    {as4_inputActive obj=$params.obj key_active='priority_on_combination_image' key_db='priority_on_combination_image' label={l s='Match cover image to combinations found' mod='pm_advancedsearch4'}}
    {if $spa_module_is_active}
        </div>
    {/if}
    {as4_inputActive obj=$params.obj key_active='add_anchor_to_url' key_db='add_anchor_to_url' label={l s='Pre-select combination found on the product page (anchor)' mod='pm_advancedsearch4'}}

    <h4>{l s='Display' mod='pm_advancedsearch4'}</h4>
    {as4_inputText obj=$params.obj key='products_per_page' label={l s='Number of products per page' mod='pm_advancedsearch4'} size='50px' defaultvalue=$products_per_page}
    {as4_select obj=$params.obj options=$options_order_by label={l s='Sort products by' mod='pm_advancedsearch4'} key='products_order_by' size='500px' onchange="handleSortChange($(this));"}
    {as4_select obj=$params.obj options=$options_order_way label={l s='Order' mod='pm_advancedsearch4'} key='products_order_way'}
    {as4_inputActive obj=$params.obj key_active='remind_selection_results' key_db='remind_selection_results' label={l s='Display a reminder of the selected criteria' mod='pm_advancedsearch4'}}
    {as4_inputActive obj=$params.obj key_active='keep_category_information' key_db='keep_category_information' label={l s='Keep description on category pages' mod='pm_advancedsearch4'}}
    {as4_richTextareaLang obj=$params.obj key='description' label={l s='Description to be displayed above the results' mod='pm_advancedsearch4'}}
{as4_endFieldset}
{* /Search results settings *}

{module->displaySubmit text="{l s='Save' d='Admin.Actions'}" name='submitSearch'}
<script type="text/javascript">
    $(document).ready(function() {
        {if !empty($params.obj->step_search)}
            $(".collapse").hide("fast");
        {else}
            $(".collapse").show("fast");
        {/if}
        handleSortChange();
    });
    displayRelatedSmartyVarOptions();
    displayRelatedFilterByEmplacementOptions();
    updateHookOptions($("#id_hook"), {$hooksId|json_encode});
    toggleSearchEngineSettings(false);
    var supportsStepSearch = {$supportsStepSearch|json_encode};
    if (!supportsStepSearch) {
        $('#search_type option[value=2]').prop('selected', false).prop('disabled', true);
    }
    window.pmAsAllowedSortOrders = {$options_order_by_allowed|json_encode};
</script>

{as4_endForm id='searchForm'}
