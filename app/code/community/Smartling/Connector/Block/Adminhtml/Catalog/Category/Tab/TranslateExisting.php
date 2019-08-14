<?php

/**
 * Description of Translations
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Catalog_Category_Tab_TranslateExisting 
    extends Smartling_Connector_Block_Adminhtml_Edit_Tab_Translations        
{
    
    /**
     *
     * @var int 
     */
    protected $_contentTypeId = 4;
    
    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {          
        return Mage::helper('connector')->showTranslationTab();
    }
    
    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/category/' . $action);
    }
    
    /**
     * 
     * @return int
     */
    public function getContentId() {
        return Mage::app()->getRequest()->getParam('id');
    }
}