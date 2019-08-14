<?php

class Smartling_Connector_Block_Adminhtml_Projects_New_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form(
                array(
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/edit', array('id' => $this->getRequest()->getParam('id'))),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
    
    public function getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/edit', array(
            '_current'   => true,
            'back'       => 'edit',
        ));
    }

}
