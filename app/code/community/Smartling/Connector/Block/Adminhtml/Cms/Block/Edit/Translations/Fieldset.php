<?php

/**
 * Description of Translations
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Cms_Block_Edit_Translations_Fieldset   
    extends Smartling_Connector_Block_Adminhtml_Edit_Tab_Translations 
{
    
    /**
     *
     * @var int 
     */
    protected $_contentTypeId = 2;
    
    /**
     * 
     * @return int
     */
    public function getContentId() {
        return Mage::app()->getRequest()->getParam('block_id');
    }
}