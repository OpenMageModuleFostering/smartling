<?php
/**
 * Description of Edit
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Instance_Edit 
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    
    /**
     * Internal constructor
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'instance_id';
        $this->_blockGroup = 'connector';
        $this->_controller = 'adminhtml_instance';
        $headerText = "Smartling Bulk Submits";
        $this->_headerText = Mage::helper('connector')->__($headerText);
        $this->_removeButton('save');
        $this->_removeButton('back');
        $this->_removeButton('reset');
    }
}