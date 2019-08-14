<?php

/**
 * Description of Tranlsations
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Edit_Tab_Translations 
    extends Mage_Adminhtml_Block_Widget_Form
        implements Mage_Adminhtml_Block_Widget_Tab_Interface 
{
      
    /**
     *
     * @var int 
     */
    protected $_contentTypeId;
    
    public function __construct() {
        parent::__construct();
        $this->setTemplate('connector/adminhtml/edit/tab/translations.phtml');                
    }
    
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
        return Mage::helper('connector')->__('Smartling Translations');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
         /* @var $model Mage_Cms_Model_Page */
        $model = Mage::registry('cms_block');
          
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
     * @return boolean | Samrtling_Connector_Model_Resource_Content_Collection
     */
    public function getContentItems() {
        $id = $this->getContentId();        
        if (!$id) {
            return false;
        }        
        $collection = Mage::getModel('connector/content')->getCollection()
                                                         ->addFieldToFilter('type', array('eq' => $this->_contentTypeId))
                                                         ->addFieldToFilter('origin_content_id', array('eq' => (int) $id));      
        return $collection;
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
     * @param int $contentTypeId
     * @param int $content_id
     * @return array
     */
    public function findAvailableLocales($contentTypeId = '', $content_id = '', $website_ids = array()) {
        return Mage::getModel('connector/projects')->getProjectsLocales($contentTypeId, $content_id, $website_ids);
    } 
    
    /**
     * 
     * @return int
     */
    public function getContentTypeId() {
        return $this->_contentTypeId;
    }
    
    /**
     * 
     * @return string
     */
    public function getTitle() {
        return Mage::helper('connector')->__("Smartling Translation Actions");
    }
    
    /**
     * 
     * @param int | float $percent
     * @return string
     */
    public function getStatusImageUrl ($percent, $includePath = false) {
        $imgSrc = '';
        if (floatval ($percent) == 0.00) {
            $imgSrc = 'wait.png';
        } elseif (floatval($percent) > 0 && floatval($percent) < 100) {
            $imgSrc = 'inprogress.png';
        } elseif (floatval($percent) == 100.00) {
            $imgSrc = 'translated.png';
        }
        
        if($includePath) {
            $themeParams = array('_area' => 'adminhtml','_theme' => 'default');
            $imgSrc = Mage::getDesign()->getSkinBaseUrl($themeParams) . 'smartling' . DS . 'images' . DS . $imgSrc;
        }
        
        return $imgSrc;
    }
    
    /**
     * 
     * @param string $id
     * @param string $label
     * @return string
     */
    public function getButtonHtmlContent($id, $label) {
        $button = Mage::app()->getLayout()->createBlock('adminhtml/widget_button');
        $button->setId($id)
               ->setLabel($label);
        return $button->toHtml();
    }
}
