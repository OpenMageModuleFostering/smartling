<?php

class Smartling_Connector_Block_Adminhtml_Instance_Tab_Attribute 
    extends Smartling_Connector_Block_Adminhtml_Instance_Widget
{
    
    public function _construct() {
        parent::_construct();
        $this->_formName = 'website_form_attribute';
        $this->_formType = 'attribute';
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
        $entity_type = 'attribute';
        
        $fieldsetProfile = $form->addFieldset('profile', array('legend' => $this->__('Profiles')));
        
        $fieldsetProfile->addType('profiles','Smartling_Connector_Block_Adminhtml_Projects_Edit_Form_Element_Profiles');
        
        $fieldsetProfile->addField('locales', 'profiles', array(
            'name'          => 'locales[]',
            'layout' => $this->getLayout(),
            'required'      => false,
            'class' => 'sl-required-entry-multy',
            'entity_type' => $entity_type
        ))
        ->setFilterWebsiteId(-1)
        ->setFilterId(-1)
        ->setEntityType($entity_type);
        
        $fieldsetProfile->addField('content_type', 'hidden', array(
                    'name' => 'content_type',
                    'value' => $this->getContentTypeId()
              ));

        return parent::_prepareForm();
    }
    
}