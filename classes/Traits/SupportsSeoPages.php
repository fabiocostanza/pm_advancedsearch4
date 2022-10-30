<?php
/**
 *
 * @author Presta-Module.com <support@presta-module.com>
 * @copyright Presta-Module
 * @license see file: LICENSE.txt
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 *
 ****/

namespace AdvancedSearch\Traits;
trait SupportsSeoPages
{
    /**
     * Helper to determine if this instance supports the SEO pages feature
     *
     * @return bool
     */
    protected static function supportsSeoPages()
    {
        return trait_exists('AdvancedSearch\Traits\SeoTrait');
    }
}
