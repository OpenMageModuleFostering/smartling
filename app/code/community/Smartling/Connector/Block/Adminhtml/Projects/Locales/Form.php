<?php

/**
 * Description of Projects Locales Form
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Projects_Locales_Form 
    extends Mage_Adminhtml_Block_Widget_Form
{
      
    /**
     *
     * @var int 
     */
    protected $_contentTypeId;
    
    /**
     * 
     * @param array $data
     */
    public function __construct(array $data) {
        parent::__construct($data);
        $this->setReadyStatus(false);
        
        if($this->getEntityType() && $this->getFilterId()) {
            
            try {
                
                $filterClass = 'connector/projects_filter_' . $this->getEntityType();
                
                $filterModel = Mage::getModel($filterClass);
                if(($filterModel instanceof Smartling_Connector_Model_Projects_Filter) === false) {
                    Mage::getSingleton('adminhtml/session')->addError(
                       Mage::helper('connector')->__("Application error. Please check smartling log to see details.")
                    );
                    Mage::throwException("Class [" . $filterClass . "] must be instance of Smartling_Connector_Model_Projects_Filter");
                }

                $options =  $filterModel->getAvailableProjects($this->getFilterId());

                if(sizeof($options) && $this->getEntityType()) {
                    $this->_formName = 'website_form_' . $this->getEntityType();

                    $this->setOptions($options);

                    $this->setTemplate('connector/adminhtml/edit/tab/bulk_translations.phtml');
                    $this->setReadyStatus(true);
                }
            } catch (Mage_Core_Exception $ex) {
                Mage::helper('connector')->log($ex->getMessage(), Zend_log::ERR);
            }
        }
    }
    
    /**
     * 
     * @param string $html
     * @return string
     */
    protected function _afterToHtml($html) {
        
        $type = $this->getEntityType();
        
        if(!$type) {
            return Mage::helper('connector')->__('Please select entity type');
        }
        
        $filter_id = $this->getFilterId();
        
        if(!$filter_id) {
            return Mage::getModel('connector/projects_filter_' . $type)->getMessageOnEmptyRequest();
        }
        
        $options = $this->getOptions();
        
        if(!sizeof($options)) {
            return Mage::helper('connector')
                        ->__('Sorry. No one profile for this item is available.');
        }
        
        return $html;
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
     * 
     * @param int $contentTypeId
     * @param int $content_id
     * @return array
     */
    public function findAvailableLocales($contentTypeId = '', $website_ids = array()) {
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
    
    public function getFormName() {
        return $this->_formName;
    }

}
