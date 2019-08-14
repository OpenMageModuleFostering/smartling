<?php

class Smartling_Connector_Block_Adminhtml_Projects_New_Tabs_Form extends Mage_Adminhtml_Block_Widget_Form 
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

    /**
     * 
     * @return \Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
        
        $form = new Varien_Data_Form(array(
                        'action' => $this->getUrl('*/*/view'),
                        'method' => 'post',
                        'id' => 'website_form'
                        ));
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('profile_website', array('legend' => $this->__('Profile Website Settings')));

        $this->_addElementTypes($fieldset);
        
        $fieldset->addField('website_id', 'select', array(
            'name' => 'website_id',
            'title' => $this->__('Website'),
            'label' => $this->__('Website'),
            'required' => true,
            'values' => Mage::getModel('connector/source_websites')->toOptionArray(),
        ));
        
        $fieldset->addField('continue_button', 'note', array(
            'text' => $this->getChildHtml('continue_button'),
        ));

        return parent::_prepareForm();
    }
    
}
