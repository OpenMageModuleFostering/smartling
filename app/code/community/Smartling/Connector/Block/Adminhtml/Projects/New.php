<?php

class Smartling_Connector_Block_Adminhtml_Projects_New extends Mage_Adminhtml_Block_Widget_Form_Container 
{

    public function __construct() {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_projects';
        $this->_blockGroup = 'connector';
        $this->_headerText = $this->_getHeaderText();

        $this->_removeButton('save');
    }

    protected function _getHeaderText() {
        return $this->__('Profile Website');
    }

}
