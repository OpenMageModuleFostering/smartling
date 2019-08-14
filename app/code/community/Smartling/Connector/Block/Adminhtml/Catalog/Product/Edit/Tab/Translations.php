<?php

/**
 * Description of Translations
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Catalog_Product_Edit_Tab_Translations 
    extends Smartling_Connector_Block_Adminhtml_Edit_Tab_Translations 
{
    
    /**
     *
     * @var int 
     */
    protected $_contentTypeId = 3;
    
    public function __construct() {
        parent::__construct();
        
        $contentId = $this->getContentId();
        $product = Mage::getSingleton('catalog/product')->load($contentId);
        
        $websites = $product->getWebsiteIds();
        $websites[] = -1; // prevent case when no one website was selected
        $this->setWebsites($websites);
    }

        /**
     * 
     * @return string | Mage_Adminhtml_Block_Widget_Form    *
     * @return true
     */
    public function canShowTab() {       
        return Mage::helper('connector')->showTranslationTab();
    }
    
    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action) {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/product/' . $action);
    }
    
    /**
     * 
     * @return int
     */
    public function getContentId() {
        return Mage::app()->getRequest()->getParam('id');
    }
    
}