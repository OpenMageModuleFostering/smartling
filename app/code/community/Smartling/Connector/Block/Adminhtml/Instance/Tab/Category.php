<?php

class Smartling_Connector_Block_Adminhtml_Instance_Tab_Category 
    extends Smartling_Connector_Block_Adminhtml_Instance_Widget
{
    
    public function _construct() {
        parent::_construct();
        $this->_formName = 'website_form_category';
        $this->_formType = 'category';
    }
    
    protected function _prepareForm()
    {
        
        $form = new Varien_Data_Form(array(
                        'action' => $this->getUrl('*/*/view'),
                        'method' => 'get',
                        'id' => $this->_formName
                        ));
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('website', array('legend' => $this->__('Website')));

        $this->_addElementTypes($fieldset);
        
        $fieldset->addField('store_group_id', 'select', array(
            'name' => 'store_group_id',
            'title' => $this->__('Store'),
            'label' => $this->__('Store'),
            'required' => true,
            'onchange' => 'getProfilesFor' . ucfirst($this->_formType) . '(this)',
            'values' => Mage::getModel('connector/source_stores')->toOptionArray(true),
                ));
        
        $fieldset->addField('content_type', 'hidden', array(
                    'name' => 'content_type',
                    'value' => $this->getContentTypeId()
              ));
        
        $fieldsetProfile = $form->addFieldset('profile', array('legend' => $this->__('Profiles')));
        
        $fieldsetProfile->addType('profiles', 'Smartling_Connector_Block_Adminhtml_Projects_Edit_Form_Element_Profiles');
        
        $fieldsetProfile->addField('stores', 'profiles', array(
            'name'          => 'stores[]',
            'layout' => $this->getLayout(),
            'required'      => false,
            'class' => 'sl-required-entry-multy',
            'entity_type' => 'category'
        ));
        
        return parent::_prepareForm();
    }
    
}