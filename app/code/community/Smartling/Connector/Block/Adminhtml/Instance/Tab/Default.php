<?php

class Smartling_Connector_Block_Adminhtml_Instance_Tab_Default 
    extends Smartling_Connector_Block_Adminhtml_Instance_Widget
{
    protected function _prepareLayout()
    {
        $this->setChild('continue_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Continue'),
                    'onclick'   => "this.form.submit()",
                    'class'     => 'save'
                    ))
                );
        return parent::_prepareLayout();
    }
    
    protected function _prepareForm()
    {
        
        $form = new Varien_Data_Form(array(
                        'action' => $this->getUrl('*/*/view'),
                        'method' => 'post',
                        'id' => 'website_form_default'
                        ));
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('website', array('legend' => $this->__('Profiles')));

        $this->_addElementTypes($fieldset);
        
        $fieldset->addField('store_ids', 'multiselect', array(
          'name' => 'store_ids[]',
          'label' => Mage::helper('connector')->__('Locales'),
          'title' => Mage::helper('connector')->__('Locales'),
          'required' => true,
          'values' => Mage::getSingleton('connector/source_projects')->toOptionArray()
        ));
        
        
        $fieldset->addField('continue_button', 'note', array(
            'text' => $this->getChildHtml('continue_button'),
        ));

        return parent::_prepareForm();
    }

}