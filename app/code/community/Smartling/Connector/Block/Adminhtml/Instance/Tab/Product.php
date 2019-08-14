<?php

class Smartling_Connector_Block_Adminhtml_Instance_Tab_Product 
    extends Smartling_Connector_Block_Adminhtml_Instance_Widget
{
    
    public function _construct() {
        parent::_construct();
        $this->_formName = 'website_form_product';
        $this->_formType = 'product';
    }
    
    protected function _prepareForm()
    {

        $form = new Varien_Data_Form(array(
                        'action' => $this->getUrl('*/*/view'),
                        'method' => 'get',
                        'id' => $this->_formName,
                        'name' => $this->_formName
                        ));
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('website', array('legend' => $this->__('Website')));

        $this->_addElementTypes($fieldset);
        
        $fieldset->addField('website_id', 'select', array(
            'name' => 'website_id',
            'title' => $this->__('Website'),
            'label' => $this->__('Website'),
            'required' => true,
            'onchange' => 'getProfilesFor' . ucfirst($this->_formType) . '(this)',
            'values' => Mage::getModel('connector/source_websites')->toOptionArray(true),
                ));
        
        $fieldset->addField('content_type', 'hidden', array(
                    'name' => 'content_type',
                    'value' => $this->getContentTypeId()
              ));

        $fieldsetProfile = $form->addFieldset('profile', array('legend' => $this->__('Profiles')));
        
        $fieldsetProfile->addType('profiles','Smartling_Connector_Block_Adminhtml_Projects_Edit_Form_Element_Profiles');
        
        $fieldsetProfile->addField('locales', 'profiles', array(
            'name'          => 'locales[]',
            'layout' => $this->getLayout(),
            'required'      => false,
            'class' => 'sl-required-entry-multy',
            'entity_type' => 'product'
        ));
        
        return parent::_prepareForm();
    }
    
}