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
trait ImageCriterionGroupTrait
{
    /**
     * Show an input to upload a file and put it in a particular destination folder, but with one file by lang
     *
     * Example : displayInlineUploadFile(array('obj' => $obj, 'key' => 'padding', 'label' => 'Padding', '/uploads/icons'));
     * Options :
     * obj as object,
     * key as string,
     * label as string,
     * destination as string the destination folder,
     * plupload as boolean use plupload swf or not (can only be true at this time...),
     * defaultvalue as mixed (default = false),
     * tips as string (default = false)
     * extend as boolean to display a checkbox 'apply to all languages'
     *
     * @param array $configOptions the options
     * @see parseOptions
     * @see displayPMFlags
     * @return string
     */
    public function displayInlineUploadFile($configOptions)
    {
        $defaultOptions = array(
            'plupload' => true,
            'filetype' => 'gif,jpg,png,jpeg,svg',
            'tips' => false,
            'extend' => false,
        );
        $configOptions = $this->parseOptions($defaultOptions, $configOptions);
        $flags = $this->displayPMFlags();
        $vars = array(
            'is_image' => preg_match('/jpg|jpeg|gif|bmp|png|svg/i', $configOptions['filetype']),
            'pm_flags' => $flags,
            'file_location_dir' => _PS_MODULE_DIR_ . DIRECTORY_SEPARATOR . $this->name . $configOptions['destination'],
        );
        return $this->fetchTemplate('core/components/input_inline_file_lang.tpl', $vars, $configOptions);
    }
    public function displayInputFileLang($configOptions)
    {
        $defaultOptions = array(
            'plupload' => true,
            'filetype' => 'gif,jpg,png,jpeg,svg',
            'tips' => false,
            'extend' => false,
        );
        $configOptions = $this->parseOptions($defaultOptions, $configOptions);
        $flag_key = $this->getKeyForLanguageFlags();
        $flags = $this->displayPMFlags($flag_key);
        $vars = array(
            'is_image' => preg_match('/jpg|jpeg|gif|bmp|png|svg/i', $configOptions['filetype']),
            'pm_flags' => $flags,
            'flag_key' => $flag_key,
            'file_location_dir' => _PS_MODULE_DIR_ . DIRECTORY_SEPARATOR . $this->name . $configOptions['destination'],
        );
        return $this->fetchTemplate('core/components/input_file_lang.tpl', $vars, $configOptions);
    }
}
