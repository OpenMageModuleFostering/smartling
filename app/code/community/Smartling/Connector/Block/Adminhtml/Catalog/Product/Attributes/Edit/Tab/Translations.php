<?php

/**
 * Description of Translations
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Catalog_Product_Attributes_Edit_Tab_Translations 
    extends Smartling_Connector_Block_Adminhtml_Edit_Tab_Translations 
{
    
    /**
     *
     * @var int 
     */
    protected $_contentTypeId = 5;
    
    /**
     * 
     * @return string | Mage_Adminhtml_Block_Widget_Form
     * @return true
     */
    public function canShowTab() {       
        return true;
    }
    
    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action) {
        return true;
    }
    
    /**
     * 
     * @return int
     */
    public function getContentId() {
        return Mage::app()->getRequest()->getParam('attribute_id');
    }
    
}