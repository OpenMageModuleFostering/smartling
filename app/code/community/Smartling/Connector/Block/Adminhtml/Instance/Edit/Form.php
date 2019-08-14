<?php

/**
 * Description of Settings
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Instance_Edit_Form 
    extends Mage_Adminhtml_Block_Widget_Form
{
    
    protected function _construct()
    {
        parent::_construct();
        $this->setActive(true);
    }
    
     /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Tab_Settings
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/view'),
            'method' => 'post'
        ));
        $form->setUseContainer(false);
        $this->setForm($form);
        return parent::_prepareForm();
    }
    
    public function getFormHtml()
    {
        return '<div id="edit_form_container"></div>';
    }
}