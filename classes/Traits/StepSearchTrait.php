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
use Validate;
use AdvancedSearch\Core;
use AdvancedSearch\Models\CriterionGroup;
trait StepSearchTrait
{
    /**
     * Determines if we are currently waiting for the selection of a first criterion in a step by step search
     *
     * @param array $row
     * @param array $row2
     * @param array $selected_criterion_groups
     * @param int $prev_id_criterion_group
     * @param array $result
     * @param int $key
     * @param int $key2
     * @return bool
     */
    protected function isFirstStep($row, $row2, $selected_criterion_groups, $prev_id_criterion_group, $result, $key, $key2)
    {
        return !(!$row['step_search'] || ($row['step_search'] && $row['step_search_next_in_disabled']) || (
            $row['step_search'] && (
                $key2 == 0 || (
                    isset($selected_criterion_groups) && (
                        in_array($row2['id_criterion_group'], $selected_criterion_groups)
                        || ($prev_id_criterion_group && in_array($prev_id_criterion_group, $selected_criterion_groups))
                        || !sizeof($result[$key]['criterions'][$prev_id_criterion_group])
                    )
                )
            )
        ));
    }
    protected function isStepSearchSliderUnavailable($row, $result, $key, $row2)
    {
        return $row['step_search']
            && $result[$key]['criterions'][$row2['id_criterion_group']][0]['min'] == 0
            && $result[$key]['criterions'][$row2['id_criterion_group']][0]['max'] == 0;
    }
    protected function setStepSearchType(&$params)
    {
        $params['obj']->search_type = 2;
    }
    protected function resetNextCriterionGroups()
    {
        $criterionsGroups = CriterionGroup::getCriterionsGroupsFromIdSearch((int)$this->idSearch, (int)$this->context->language->id, false);
        if (Core::isFilledArray($criterionsGroups)) {
            $deleteAfter = false;
            foreach ($criterionsGroups as $criterionGroup) {
                if ((int)$criterionGroup['id_criterion_group'] == $this->reset_group) {
                    $deleteAfter = true;
                }
                if ($deleteAfter && isset($this->criterions[(int)$criterionGroup['id_criterion_group']])) {
                    unset($this->criterions[(int)$criterionGroup['id_criterion_group']]);
                }
            }
        }
    }
    protected static function prepareNewCriterionGroupForStepSearchIndexation(&$objAdvancedSearchCriterionGroupClass)
    {
        if ($objAdvancedSearchCriterionGroupClass->criterion_group_type == 'category' && !empty($objAdvancedSearchCriterionGroupClass->id_criterion_group_linked) && !Validate::isLoadedObject($objAdvancedSearchCriterionGroupClass)) {
            $objAdvancedSearchCriterionGroupClass->only_children = 1;
        }
    }
}
