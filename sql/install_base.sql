CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedsearch` (
  `id_search` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_hook` int(10) unsigned NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `internal_name` varchar(255) NOT NULL,
  `css_classes` varchar(255) DEFAULT 'col-xs-12',
  `search_results_selector_css` varchar(255) DEFAULT '',
  `display_nb_result_on_blc` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `display_nb_result_criterion` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `remind_selection` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `show_hide_crit_method` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `filter_by_emplacement` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `search_on_stock` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `hide_empty_crit_group` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `search_method` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `step_search` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `step_search_next_in_disabled` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `position` smallint(4) unsigned NULL DEFAULT '0',
  `unique_search` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `scrolltop_active` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `id_category_root` int(10) unsigned NOT NULL DEFAULT '0',
  `redirect_one_product` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `add_anchor_to_url` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `reset_group` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `insert_in_center_column` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `smarty_var_name` varchar(64) NOT NULL,
  `search_results_selector` varchar(64) NOT NULL DEFAULT '#center_column',
  `recursing_indexing` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `display_empty_criteria` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `keep_category_information` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `priority_on_combination_image` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `products_per_page` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `products_order_by` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `products_order_way` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `hide_criterions_group_with_no_effect` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_search`),
  KEY `id_hook` (`id_hook`),
  KEY `active` (`active`),
  KEY `position` (`position`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedsearch_lang` (
  `id_search` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id_search`, `id_lang`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedsearch_shop` (
  `id_search` int(11) NOT NULL,
  `id_shop` int(11) NOT NULL,
  PRIMARY KEY (`id_search`, `id_shop`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedsearch_category` (
  `id_search` int(10) unsigned NOT NULL,
  `id_category` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_search`, `id_category`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedsearch_cms` (
  `id_search` int(10) unsigned NOT NULL,
  `id_cms` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_search`, `id_cms`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedsearch_products` (
  `id_search` int(10) unsigned NOT NULL,
  `id_product` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_search`, `id_product`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedsearch_products_cat` (
  `id_search` int(10) unsigned NOT NULL,
  `id_category` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_search`, `id_category`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedsearch_manufacturers` (
  `id_search` int(10) unsigned NOT NULL,
  `id_manufacturer` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_search`, `id_manufacturer`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedsearch_suppliers` (
  `id_search` int(10) unsigned NOT NULL,
  `id_supplier` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_search`, `id_supplier`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedsearch_special_pages` (
  `id_search` int(10) unsigned NOT NULL,
  `page` varchar(255) NOT NULL,
  PRIMARY KEY (`id_search`, `page`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedsearch_seo` (
  `id_seo` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_search` int(10) unsigned NOT NULL,
  `id_currency` int(10) unsigned NOT NULL,
  `criteria` text NOT NULL,
  `seo_key` varchar(32) NOT NULL,
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_seo`),
  KEY `id_search` (`id_search`),
  KEY `deleted` (`deleted`),
  UNIQUE KEY `seo_key` (`seo_key`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedsearch_seo_lang` (
  `id_seo` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `meta_title` varchar(128) NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `meta_keywords` varchar(255) NOT NULL,
  `title` varchar(128) NOT NULL,
  `description` text NOT NULL,
  `seo_url` varchar(128) NOT NULL,
  PRIMARY KEY (`id_seo`, `id_lang`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedsearch_seo_crosslinks` (
  `id_seo` int(10) unsigned NOT NULL,
  `id_seo_linked` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_seo`, `id_seo_linked`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=latin1;