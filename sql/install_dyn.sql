DROP TABLE IF EXISTS `PREFIX_pm_advancedsearch_criterion_group_ID_SEARCH`;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedsearch_criterion_group_ID_SEARCH` (
  `id_criterion_group` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_search` int(10) UNSIGNED NOT NULL,
  `criterion_group_type` varchar(24) NOT NULL,
  `sort_by` varchar(10) DEFAULT 'position',
  `sort_way` varchar(4) DEFAULT 'ASC',
  `id_criterion_group_linked` int(10) UNSIGNED NOT NULL,
  `display_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `context_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 2,
  `is_multicriteria` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `filter_option` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `is_combined` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `range` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `range_nb` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 15.00,
  `show_all_depth` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `only_children` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `hidden` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `max_display` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `css_classes` varchar(255) DEFAULT 'col-xs-12 col-sm-3',
  `overflow_height` smallint(4) UNSIGNED NOT NULL DEFAULT 0,
  `position` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `visible` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_criterion_group`),
  KEY `id_search` (`id_search`, `visible`, `position`)
) ENGINE=MYSQL_ENGINE  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `PREFIX_pm_advancedsearch_criterion_group_ID_SEARCH_lang`;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedsearch_criterion_group_ID_SEARCH_lang` (
  `id_criterion_group` int(10) UNSIGNED NOT NULL,
  `id_lang` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `url_identifier` varchar(255) NOT NULL,
  `url_identifier_original` varchar(255) NOT NULL,
  `icon` varchar(32) NOT NULL,
  `range_sign` varchar(32) DEFAULT NULL,
  `range_interval` varchar(255) DEFAULT NULL,
  `all_label` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_criterion_group`, `id_lang`),
  KEY `url_identifier` (`url_identifier`),
  KEY `url_identifier_original` (`url_identifier_original`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `PREFIX_pm_advancedsearch_criterion_ID_SEARCH`;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedsearch_criterion_ID_SEARCH` (
  `id_criterion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_criterion_group` int(10) UNSIGNED NOT NULL,
  `position` int(10) UNSIGNED DEFAULT 0,
  `color` varchar(255) DEFAULT NULL,
  `level_depth` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `id_parent` int(10) UNSIGNED DEFAULT NULL,
  `visible` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `is_custom` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_criterion`),
  KEY `id_criterion_group` (`id_criterion_group`),
  KEY `level_depth` (`level_depth`),
  KEY `position` (`position`),
  KEY `id_parent` (`id_parent`),
  KEY `visible` (`visible`),
  KEY `is_custom` (`is_custom`),
  KEY `gcfsb_1` (`id_criterion`, `id_criterion_group`, `visible`, `position`)
) ENGINE=MYSQL_ENGINE  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `PREFIX_pm_advancedsearch_criterion_ID_SEARCH_link`;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedsearch_criterion_ID_SEARCH_link` (
  `id_criterion` int(10) UNSIGNED NOT NULL,
  `id_criterion_linked` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_criterion`, `id_criterion_linked`),
  KEY `id_criterion` (`id_criterion`),
  KEY `id_criterion_linked` (`id_criterion_linked`)
) ENGINE=MYSQL_ENGINE  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `PREFIX_pm_advancedsearch_criterion_ID_SEARCH_list`;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedsearch_criterion_ID_SEARCH_list` (
  `id_criterion_parent` int(10) UNSIGNED NOT NULL,
  `id_criterion` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_criterion_parent`, `id_criterion`),
  KEY `id_criterion_parent` (`id_criterion_parent`),
  KEY `id_criterion` (`id_criterion`)
) ENGINE=MYSQL_ENGINE  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `PREFIX_pm_advancedsearch_criterion_ID_SEARCH_lang`;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedsearch_criterion_ID_SEARCH_lang` (
  `id_criterion` int(10) UNSIGNED NOT NULL,
  `id_lang` int(10) UNSIGNED NOT NULL,
  `value` varchar(255) NOT NULL,
  `decimal_value` decimal(20, 6) NULL DEFAULT NULL,
  `url_identifier` varchar(255) NOT NULL,
  `url_identifier_original` varchar(255) NOT NULL,
  `icon` varchar(32) NOT NULL,
  PRIMARY KEY (`id_criterion`, `id_lang`),
  KEY `value` (`value`),
  KEY `url_identifier` (`url_identifier`),
  KEY `url_identifier_original` (`url_identifier_original`),
  KEY `decimal_value` (`decimal_value`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `PREFIX_pm_advancedsearch_cache_product_criterion_ID_SEARCH`;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedsearch_cache_product_criterion_ID_SEARCH` (
  `id_criterion` int(10) UNSIGNED NOT NULL,
  `id_cache_product` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_criterion`, `id_cache_product`),
  KEY `id_criterion2` (`id_criterion`),
  KEY `id_cache_product` (`id_cache_product`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `PREFIX_pm_advancedsearch_cache_product_ID_SEARCH`;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedsearch_cache_product_ID_SEARCH` (
  `id_cache_product` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_product` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_cache_product`),
  UNIQUE KEY `id_product` (`id_product`)
) ENGINE=MYSQL_ENGINE  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `PREFIX_pm_advancedsearch_product_price_ID_SEARCH`;

CREATE TABLE IF NOT EXISTS `PREFIX_pm_advancedsearch_product_price_ID_SEARCH` (
  `id_cache_product` int(10) UNSIGNED NOT NULL,
  `id_shop` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `id_currency` int(10) UNSIGNED NOT NULL,
  `id_country` int(10) UNSIGNED NOT NULL,
  `id_group` int(10) UNSIGNED NOT NULL,
  `price_wt` decimal(20, 6) NOT NULL,
  `reduction_amount` decimal(20, 6) NOT NULL DEFAULT 0.000000,
  `reduction_type` enum('amount','percentage') NULL DEFAULT NULL,
  `reduction_tax` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `from` datetime NOT NULL,
  `to` datetime NOT NULL,
  `is_specific` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `has_no_specific` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `id_specific_price` int(10) UNSIGNED DEFAULT NULL,
  `valid_id_specific_price` int(10) UNSIGNED DEFAULT 0,
  PRIMARY KEY (`id_cache_product`, `id_currency`, `id_country`, `id_group`, `price_wt`, `from`, `to`, `reduction_amount`),
  KEY `id_cache_product` (`id_cache_product`),
  KEY `is_specific` (`is_specific`),
  KEY `has_no_specific` (`has_no_specific`),
  KEY `valid_id_specific_price` (`valid_id_specific_price`)
) ENGINE=MYSQL_ENGINE DEFAULT CHARSET=latin1;
