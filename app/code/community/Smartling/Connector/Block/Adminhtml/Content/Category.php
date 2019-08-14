<?php

/**
 * Description of Category
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Content_Category 
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    
    public function __construct() {
        parent::__construct();
        $this->_controller = "adminhtml_content_category";
        $this->_blockGroup = "connector";
        
        if ($this->getRequest()->getActionName() == 'confirm'){
            $this->_headerText = Mage::helper('connector')->__('Confirm category items');
            $this->_addButton('save', array(
                'label'     => Mage::helper('adminhtml')->__('Add to translation queue'),
                'onclick'   => "setLocation('".$this->getUrl('*/*/save', array('_current' => true))."')",
                'class'     => 'go'
            ));
        } else {
            
            $this->_addButton('back', array(
                'label' => Mage::helper('adminhtml')->__('Back'),
                'onclick' => "setLocation('" . $this->getUrl('*/*/edit') . "')",
                'class' => 'back'
            ));

            $this->_headerText = Mage::helper('connector')->__('Categories content list');            
        }
        $this->_removeButton('add');
        
        
    }
}
