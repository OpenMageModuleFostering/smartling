<?php

/**
 * Description of IndexController
 *
 * @author Smartling
 */
class Smartling_Connector_Adminhtml_FieldsController 
    extends Mage_Adminhtml_Controller_Action
    implements Smartling_Connector_Model_Translate_StatusInterface 
{
    
    protected function _initAction()
    {
        $this->loadLayout()
             ->renderLayout();
        return $this;
    }
    
    public function indexAction()
    {
        $this->pagesAction();
    }

    public function pagesAction()
    {
        $this->_initAction();
    }
    
    
    public function blocksAction()
    {
        $this->_initAction();
    }
    
    /**
     * 
     * @return Zend_Controller_Response_Abstract
     */
    public function changeStatusAction() {
        
        $requestData = $this->getRequest()->getParams();
        
        $result = Mage::helper('connector')->changeStatus($requestData['type'], 
                                                         $requestData['id'], 
                                                         'fields',
                                                         'field_name');
        
        return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
}
