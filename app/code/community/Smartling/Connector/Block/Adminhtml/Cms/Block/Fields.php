<?php

/**
 * Description of Content
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Cms_Block_Fields 
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    
    public function __construct() {
                
        $this->_controller = "adminhtml_cms_block_fields";
        $this->_blockGroup = "connector";
        $this->_headerText = Mage::helper('connector')->__('CMS Block Fields');
                
        parent::__construct();
        $this->_removeButton('add');
    }
    
    public function getCreateUrl()
    {
        return false;
    }
}
