<?php

/**
 * Description of Content
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Catalog_Category_Attributes
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    
    public function __construct() {
                
        $this->_controller = "adminhtml_catalog_category_attributes";
        $this->_blockGroup = "connector";
        $this->_headerText = Mage::helper('connector')->__('Category Attributes');        
                
        parent::__construct();
        $this->_removeButton('add');
    }
    
    public function getCreateUrl()
    {
        return false;
    }
}
