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
trait SupportsStepSearch
{
    /**
     * Helper to determine if the current module instance supports step by step search
     *
     * @return bool
     */
    protected static function supportsStepSearch()
    {
        return trait_exists('AdvancedSearch\Traits\StepSearchTrait');
    }
}
