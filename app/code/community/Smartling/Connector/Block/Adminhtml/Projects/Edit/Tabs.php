<?php

class Smartling_Connector_Block_Adminhtml_Projects_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('projects_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Settings Profile'));
    }

    protected function _beforeToHtml() {
        $this->addTab('general', array(
            'label' => $this->__('General'),
            'title' => $this->__('General'),
            'content' => $this->getLayout()->createBlock('connector/adminhtml_projects_edit_tabs_form')->toHtml(),
                )
        );

        return parent::_beforeToHtml();
    }

}
