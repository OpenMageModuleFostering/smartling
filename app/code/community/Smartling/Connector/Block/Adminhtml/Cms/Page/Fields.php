<?php

/**
 * Description of Content
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Cms_Page_Fields 
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    
    public function __construct() {
                
        $this->_controller = "adminhtml_cms_page_fields";
        $this->_blockGroup = "connector";
        $this->_headerText = Mage::helper('connector')->__('CMS Page Fields');        
                
        parent::__construct();
        $this->_removeButton('add');
    }
    
    public function getCreateUrl()
    {
        return false;
    }
}
