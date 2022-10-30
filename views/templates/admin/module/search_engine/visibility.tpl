{as4_startForm id='searchForm' obj=$params.obj params=$params}
{module->displayTitle text="{l s='Visibility restrictions' mod='pm_advancedsearch4'}"}

{module->showWarning text="
    {l s='If you want to restrict the search engine display to some pages of your shop, please make your selection below.' mod='pm_advancedsearch4'}<br />
    {l s='Leaving theses settings empty will display your search engine everywhere on your shop.' mod='pm_advancedsearch4'}<br /><br />
    {l s='For more info, please refer to [1]our FAQ[/1]' mod='pm_advancedsearch4' tags=['<a href="https://faq.presta-module.com/faq/328" target="_blank">', '</a>']}
"}

{* Categories *}
{as4_inputActive obj=$params.obj key_active='bool_cat' key_db='bool_cat' label={l s='Restrict the search engine display to some categories' mod='pm_advancedsearch4'} defaultvalue="{if $categories_association|is_array && $categories_association|sizeof}1{else}0{/if}" onclick='display_cat_picker();'}
<div id="category_picker" class="col-sm-offset-4 offset-sm-4">
    {as4_categoryTree label={l s='Categories' mod='pm_advancedsearch4'} input_name='categories_association' selected_cat=$categories_association category_root_id=$root_category_id}
</div>

{* Products *}
{as4_inputActive obj=$params.obj key_active='bool_prod' key_db='bool_prod' label={l s='Restrict the search engine display to some products (by selecting products)' mod='pm_advancedsearch4'} defaultvalue="{if $products_association|is_array && $products_association|sizeof}1{else}0{/if}" onclick='display_prod_picker();'}
<div id="product_picker">
    {as4_ajaxSelectMultiple selectedoptions=$products_association key='products_association' label={l s='Products' mod='pm_advancedsearch4'} remoteurl="{$base_config_url|as4_nofilter}&getItem=1&itemType=product" idcolumn='id_product' namecolumn='name'}
</div>

{* Product's category *}
{as4_inputActive obj=$params.obj key_active='bool_cat_prod' key_db='bool_cat_prod' label={l s='Restrict the search engine display to some products (by selecting parent categories)' mod='pm_advancedsearch4'} defaultvalue="{if $product_categories_association|is_array && $product_categories_association|sizeof}1{else}0{/if}" onclick='display_cat_prod_picker();'}
<div id="category_product_picker" class="col-sm-offset-4 offset-sm-4">
    {as4_categoryTree label={l s='Categories' mod='pm_advancedsearch4'} input_name='product_categories_association' selected_cat=$product_categories_association category_root_id=$root_category_id}
</div>

{* Manufacturers *}
{as4_inputActive obj=$params.obj key_active='bool_manu' key_db='bool_manu' label={l s='Restrict the search engine display to some manufacturers' mod='pm_advancedsearch4'} defaultvalue="{if $manufacturers_association|is_array && $manufacturers_association|sizeof}1{else}0{/if}" onclick='display_manu_picker();'}
<div id="manu_picker">
    {as4_ajaxSelectMultiple selectedoptions=$manufacturers_association key='manufacturers_association' label={l s='Manufacturers' mod='pm_advancedsearch4'} remoteurl="{$base_config_url|as4_nofilter}&getItem=1&itemType=manufacturer" idcolumn='id_manufacturer' namecolumn='name'}
</div>

{* Suppliers *}
{as4_inputActive obj=$params.obj key_active='bool_supp' key_db='bool_supp' label={l s='Restrict the search engine display to some suppliers' mod='pm_advancedsearch4'} defaultvalue="{if $suppliers_association|is_array && $suppliers_association|sizeof}1{else}0{/if}" onclick='display_supp_picker();'}
<div id="supp_picker">
    {as4_ajaxSelectMultiple selectedoptions=$suppliers_association key='suppliers_association' label={l s='Suppliers' mod='pm_advancedsearch4'} remoteurl="{$base_config_url|as4_nofilter}&getItem=1&itemType=supplier" idcolumn='id_supplier' namecolumn='name'}
</div>

{* CMS *}
{as4_inputActive obj=$params.obj key_active='bool_cms' key_db='bool_cms' label={l s='Restrict the search engine display to some CMS pages' mod='pm_advancedsearch4'} defaultvalue="{if $cms_association|is_array && $cms_association|sizeof}1{else}0{/if}" onclick='display_cms_picker();'}
<div id="cms_picker">
    {as4_ajaxSelectMultiple selectedoptions=$cms_association key='cms_association' label={l s='CMS pages' mod='pm_advancedsearch4'} remoteurl="{$base_config_url|as4_nofilter}&getItem=1&itemType=cms" idcolumn='id_cms' namecolumn='meta_title'}
</div>

{* Controller, special pages *}
{as4_inputActive obj=$params.obj key_active='bool_spe' key_db='bool_spe' label={l s='Restrict the search engine display to some special pages' mod='pm_advancedsearch4'} defaultvalue="{if $special_pages_association|is_array && $special_pages_association|sizeof}1{else}0{/if}" onclick='display_spe_picker();'}
<div id="special_pages">
    {as4_ajaxSelectMultiple selectedoptions=$special_pages_association key='special_pages_association' label={l s='Special pages' mod='pm_advancedsearch4'} remoteurl="{$base_config_url|as4_nofilter}&getItem=1&itemType=controller" idcolumn='page' namecolumn='title'}
</div>

{module->displaySubmit text="{l s='Save' d='Admin.Actions'}" name='submitSearchVisibility'}
<br /><br />
<script type="text/javascript">
    display_cms_picker();
    display_spe_picker();
    display_cat_picker();
    display_cat_prod_picker();
    display_prod_picker();
    display_manu_picker();
    display_supp_picker();
</script>
{as4_endForm id='searchForm'}
