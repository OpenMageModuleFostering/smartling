<?php

/**
 * Description of Massaction
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Grid_Massaction 
    extends Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract
{
    protected $_action = '';
    
    public function __construct()
    {
        parent::__construct();        
        $this->setTemplate('connector/adminhtml/widget/grid/massaction.phtml');
        $this->setErrorText(Mage::helper('catalog')->jsQuoteEscape(Mage::helper('catalog')->__('Please select items.')));
    }
    
    /**
     * Retrieve apply button html
     *
     * @return string
     */
    public function getApplyButtonHtml()
    {
        return $this->getButtonHtml($this->__('Continue'), $this->getJsObjectName() . ".apply()");
    }
    
    /**
     * 
     * @return string
     */
    public function getAction() {
        return $this->_action;
    }
    
    /**
     * 
     * @param string $url
     * @return string
     */
    public function setAction($url) {
        $this->_action = $url;
        return $this->_action;
    }
}