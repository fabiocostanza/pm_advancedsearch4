<?php
/**
 *
 * @author Presta-Module.com <support@presta-module.com>
 * @copyright Presta-Module
 * @license   Commercial
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 *
 ****/

if (!defined('_PS_VERSION_')) {
    exit;
}
class pm_advancedsearch4searchresultsModuleFrontController extends ModuleFrontController
{
    // Récupérer la config thème des catégories et l'associer à ce controlleur (colonnes)
    // Remplacer s-<id_search> par le nom du moteur ?
    protected $idSearch;
    protected $searchInstance;
    protected $currentIdCategory;
    protected $currentIdManufacturer;
    protected $currentIdSupplier;
    protected $criterionsList = array();
    public function init()
    {
        parent::init();
        if (!headers_sent()) {
            header('X-Robots-Tag: noindex', true);
        }
        $this->idSearch = (int)Tools::getValue('id_search');
        $this->searchInstance = new AdvancedSearchClass((int)$this->idSearch, (int)$this->context->cookie->id_lang);
        if (!Validate::isLoadedObject($this->searchInstance)) {
            Tools::redirect('404');
        } else {
            if (!$this->searchInstance->active) {
                header("Status: 307 Temporary Redirect", false, 307);
                Tools::redirect('index');
            }
        }
        $this->currentIdCategory = As4SearchEngine::getCurrentCategory();
        $this->currentIdManufacturer = As4SearchEngine::getCurrentManufacturer();
        $this->currentIdSupplier = As4SearchEngine::getCurrentSupplier();
        if (Tools::getValue('as4_from') == 'category' && empty($this->currentIdCategory)) {
            Tools::redirect('404');
        } elseif (Tools::getValue('as4_from') == 'manufacturer' && empty($this->currentIdManufacturer)) {
            Tools::redirect('404');
        } elseif (Tools::getValue('as4_from') == 'supplier' && empty($this->currentIdSupplier)) {
            Tools::redirect('404');
        }
        $this->setCriterions();
        $this->setSmartyVars();
        $this->setTemplate('search-results.tpl');
    }
    public function getSelectedCriterions()
    {
        return $this->criterionsList;
    }
    protected function setCriterions()
    {
        $searchQuery = trim(Tools::getValue('as4_sq'));
        if (!empty($searchQuery)) {
            $this->criterionsList = As4SearchEngine::getCriterionsFromURL($this->idSearch, $searchQuery);
            if ($this->searchInstance->filter_by_emplacement) {
                $criterionsFromEmplacement = As4SearchEngine::getCriteriaFromEmplacement($this->searchInstance->id);
                foreach ($criterionsFromEmplacement as $idCriterionGroup => $idCriterionList) {
                    if (!isset($this->criterionsList[$idCriterionGroup])) {
                        $this->criterionsList[$idCriterionGroup] = $idCriterionList;
                    } else {
                        $this->criterionsList[$idCriterionGroup] = $this->criterionsList[$idCriterionGroup] + $idCriterionList;
                    }
                }
            }
            $this->criterionsList = As4SearchEngine::cleanArrayCriterion($this->criterionsList);
            if (!sizeof($this->criterionsList)) {
                Tools::redirect('404');
            } else {
                if (!headers_sent()) {
                    header('Link: <' . As4SearchEngine::generateURLFromCriterions($this->idSearch, $this->criterionsList) . '>; rel="canonical"', true);
                }
            }
        } else {
            Tools::redirect('404');
        }
    }
    public function setMedia()
    {
        parent::setMedia();
        if ((method_exists($this->context, 'getMobileDevice') && $this->context->getMobileDevice() == false || !method_exists($this->context, 'getMobileDevice'))) {
            $this->addCSS(array(
                _THEME_CSS_DIR_.'scenes.css' => 'all',
                _THEME_CSS_DIR_.'category.css' => 'all',
                _THEME_CSS_DIR_.'product_list.css' => 'all',
            ));
            if (Configuration::get('PS_COMPARATOR_MAX_ITEM') > 0) {
                $this->addJS(_THEME_JS_DIR_.'products-comparison.js');
            }
        }
    }
    protected function setSmartyVars()
    {
        $pageNb = (int)Tools::getValue('p', 1);
        $this->module->setProductFilterContext();
        $nb_products = As4SearchEngine::getProductsSearched(
            $this->idSearch,
            $this->criterionsList,
            As4SearchEngine::getCriterionGroupsTypeAndDisplay($this->idSearch, array_keys($this->criterionsList)),
            null,
            null,
            true
        );
        $products = As4SearchEngine::getProductsSearched(
            $this->idSearch,
            $this->criterionsList,
            As4SearchEngine::getCriterionGroupsTypeAndDisplay($this->idSearch, array_keys($this->criterionsList)),
            (int)$pageNb,
            (int)Tools::getValue('n', $this->searchInstance->products_per_page),
            false
        );
        $this->module->_assignPagination($this->searchInstance->products_per_page, $nb_products);
        $this->module->_assignProductSort($this->searchInstance);
        $link_pm = new LinkPM($this->context->link->protocol_link, $this->context->link->protocol_content);
        if ($this->currentIdCategory) {
            if ($this->currentIdCategory == (int)Context::getContext()->shop->getCategory()) {
                $this->context->smarty->assign(array(
                    'path' => As4SearchEngine::getCategoryName($this->currentIdCategory, (int)$this->context->cookie->id_lang),
                ));
            } else {
                $this->context->smarty->assign(array(
                    'path' => Tools::getPath($this->currentIdCategory),
                ));
            }
        } elseif ($this->currentIdManufacturer) {
            $manufacturer = new Manufacturer($this->currentIdManufacturer, (int)$this->context->cookie->id_lang);
            $this->context->smarty->assign(array(
                'path' => $manufacturer->name,
            ));
        } elseif ($this->currentIdSupplier) {
            $supplier = new Supplier($this->currentIdSupplier, (int)$this->context->cookie->id_lang);
            $this->context->smarty->assign(array(
                'path' => $supplier->name,
            ));
        } else {
            $this->context->smarty->assign(array(
                'path' => $this->searchInstance->title,
            ));
        }
        $this->context->smarty->assign(array(
            'products' => $products,
            'nb_products' => $nb_products,
            'id_search' => $this->idSearch,
            'request' => $link_pm->getPaginationLink(false, false, false, true),
            'link' => $link_pm,
            'as_seo_description' => $this->searchInstance->description,
            'as_seo_title' => $this->searchInstance->title,
            'nobots' => true,
        ));
    }
    public function getSearchEngine()
    {
        return $this->searchInstance;
    }
    public function getCriterionsList()
    {
        return $this->criterionsList;
    }
}
