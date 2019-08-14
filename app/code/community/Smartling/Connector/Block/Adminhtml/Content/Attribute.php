<?php

/**
 * Description of Product
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Content_Attribute 
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    
    /**
     * Defines default parameters
     *  
     */
    public function __construct() {
        parent::__construct();
        $this->_controller = "adminhtml_content_attribute";
        $this->_blockGroup = "connector";
        
        if ($this->getRequest()->getActionName() == 'confirm'){
            $this->_headerText = Mage::helper('connector')->__('Confirm attributes items');
            
            $this->_addButton('back', array(
                'label' => Mage::helper('adminhtml')->__('Back'),
                'onclick' => "setLocation('" . $this->getUrl('*/*/view', array('_current'=>true)) . "')",
                'class' => 'back'
            ));
            
            $this->_addButton('save', array(
                'label'     => Mage::helper('adminhtml')->__('Add to translation queue'),
                'onclick'   => "setLocation('".$this->getUrl('*/*/save')."')",
                'class'     => 'go'
            ));
            
        } else {
            
            $this->_addButton('back', array(
                'label' => Mage::helper('adminhtml')->__('Back'),
                'onclick' => "setLocation('" . $this->getUrl('*/*/edit') . "')",
                'class' => 'back'
            ));
            
            $this->_headerText = Mage::helper('connector')->__('Attributes content list');            
        }
        $this->_removeButton('add');
        
    }
}