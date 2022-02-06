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
class pm_advancedsearch4seositemapModuleFrontController extends ModuleFrontController
{
    private $idSearch;
    private $searchInstance;
    public function init()
    {
        if (ob_get_length() > 0) {
            ob_clean();
        }
        header('Content-type: text/xml');
        $this->idSearch = (int)Tools::getValue('id_search');
        $this->searchInstance = new AdvancedSearchClass((int)$this->idSearch, (int)$this->context->language->id);
        if (!Validate::isLoadedObject($this->searchInstance)) {
            Tools::redirect('404');
        }
        $xmlSiteMapHeader = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
</urlset>
XML;
        $xml = new SimpleXMLElement($xmlSiteMapHeader);
        foreach (Language::getLanguages(true, (int)$this->context->shop->id) as $language) {
            $seoSearchs = AdvancedSearchSeoClass::getSeoSearchs($language['id_lang'], false, $this->idSearch);
            foreach ($seoSearchs as $seoSearch) {
                $nbCriteria = count(unserialize($seoSearch['criteria']));
                if ($nbCriteria <= 3) {
                    $priority = 0.7;
                } elseif ($nbCriteria <= 5) {
                    $priority = 0.6;
                } else {
                    $priority = 0.5;
                }
                $sitemap = $xml->addChild('url');
                $sitemap->addChild('loc', $this->context->link->getModuleLink('pm_advancedsearch4', 'seo', array('id_seo' => (int)$seoSearch['id_seo'], 'seo_url' => $seoSearch['seo_url']), null, (int)$language['id_lang']));
                $sitemap->addChild('priority', $priority);
                $sitemap->addChild('changefreq', 'weekly');
            }
        }
        die($xml->asXML());
    }
}
