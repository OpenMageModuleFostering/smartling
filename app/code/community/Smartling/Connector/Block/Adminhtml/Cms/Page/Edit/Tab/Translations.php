<?php

/**
 * Description of Tranlsations
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Cms_Page_Edit_Tab_Translations 
    extends Smartling_Connector_Block_Adminhtml_Edit_Tab_Translations        
{
      
    /**
     *
     * @var int 
     */
    protected $_contentTypeId = 1;   

    
    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('connector')->__('Smartling Translations');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('cms')->__('Smartling Translations');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
         /* @var $model Mage_Cms_Model_Page */
        $model = Mage::registry('cms_page');
                
        if ($storeIds = $model->getStoreId()){
            
            if (in_array(0, $storeIds)){
                return true;
            }
            
            $defaultStoreId = Mage::helper('connector')->getDefaultStoreId();
            if (!in_array($defaultStoreId, $model->getStoreId())){
                return false;
            }
        }
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('cms/page/' . $action);
    }
    
    /**
     * 
     * @return int
     */
    public function getContentId() {
        return Mage::app()->getRequest()->getParam('page_id');
    }
    
    /**
     * 
     * @return int
     */
    public function getContentTypeId () {
        return $this->_contentTypeId;
    }
    
    /**
     * 
     * @return string
     */
    public function getTitle() {
        return Mage::helper('connector')->__("Smartling Translation Actions");
    }    
}
