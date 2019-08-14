<?php

/**
 * Description of ConnectionButton
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_System_Config_ConnectionButton 
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    
    /**
     * 
     * set template
     */
    public function _construct() {
        $this->setTemplate('connector/adminhtml/system/config/connectionbutton.phtml');
    }
    
    /**
     * 
     * @param \Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(\Varien_Data_Form_Element_Abstract $element) {
        return $this->_toHtml();    
    }
    
    /**
     * 
     * @return string
     */
    public function getServiceUrl(){
        return $this->getUrl('smartling/adminhtml_service/configTest', array('limit' => 1));
    }
    
    /**
     * 
     * @return string
     */
    public function getButton(){
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel('Test')
                    ->setOnClick("testConnection()");
                    
        return $button->toHtml();     
    }
}