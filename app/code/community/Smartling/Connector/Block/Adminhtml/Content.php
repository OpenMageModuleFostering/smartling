<?php

/**
 * Description of Content
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Content 
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    
    public function __construct() {
        parent::__construct();        
        $this->_controller = "adminhtml_content";
        $this->_blockGroup = "connector";
        $this->_headerText = Mage::helper('connector')->__('Contents list');        
        $this->_removeButton('add');
    }
}
