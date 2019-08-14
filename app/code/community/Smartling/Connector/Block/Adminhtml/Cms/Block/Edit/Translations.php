<?php

/**
 * Description of Translations
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Cms_Block_Edit_Translations 
    extends Varien_Data_Form_Element_Abstract
{
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setType('label');
    }

    /**
     * 
     * @return string
     */
    public function getElementHtml()
    {
        return '';
    }

    /**
     * 
     * @param string $idSuffix
     * @return string
     */
    public function getLabelHtml($idSuffix = '') {
        
        $translations = Mage::app()->getLayout()
                        ->createBlock('connector/adminhtml_cms_block_edit_translations_fieldset');
        return $translations->toHtml();
    }
}