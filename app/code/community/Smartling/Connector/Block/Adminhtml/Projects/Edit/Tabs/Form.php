<?php

class Smartling_Connector_Block_Adminhtml_Projects_Edit_Tabs_Form extends Mage_Adminhtml_Block_Widget_Form 
{

    /**
     * 
     * @return \Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
        
        $website_id = Mage::registry('website_id');
        $websiteInfo = Mage::app()->getWebsite($website_id);
        
        $form = new Varien_Data_Form();

        $this->setForm($form);

        $fieldset = $form->addFieldset('projects_general', array('legend' => $this->__('General Information')));

        $this->_addElementTypes($fieldset);

        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => $this->__('Profile Name'),
            'title' => $this->__('Profile Name'),
            'required' => true,
            'class' => 'required-entry',
        ));
        
        $fieldset->addField('active', 'select', array(
            'name' => 'active',
            'title' => $this->__('Active'),
            'label' => $this->__('Active'),
            'required' => true,
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $fieldset->addField('api_url', 'text', array(
            'name' => 'api_url',
            'label' => $this->__('API URL'),
            'title' => $this->__('API URL'),
            'required' => true,
            'class' => 'required-entry',
        ));
        
        $fieldset->addField('callback_url', 'text', array(
            'name' => 'callback_url',
            'label' => $this->__('Callback URL'),
            'title' => $this->__('Callback URL'),
            'required' => false
        ));
        
        $fieldset->addField('project_id', 'text', array(
            'name' => 'project_id',
            'label' => $this->__('Project Id'),
            'title' => $this->__('Project Id'),
            'required' => true,
            'class' => 'required-entry',
        ));

        $fieldset->addField('key', 'text', array(
            'name' => 'key',
            'label' => $this->__('API Key'),
            'title' => $this->__('API Key'),
            'required' => true,
        ));
        
        $fieldset->addField('retrieval_type', 'select', array(
            'name' => 'retrieval_type',
            'label' => $this->__('Retrieval Type'),
            'title' => $this->__('Retrieval Type'),
            'required' => true,
            'values' => Mage::helper('connector')->getRetrievalTypes()
        ));
        
        $fieldset->addField('test_connection', 'button', array('label' => Mage::helper('connector')->__('Test Connection'), 'name' => 'test'))
                 ->setRenderer(
                         $this->getLayout()->createBlock('connector/adminhtml_system_config_connectionButton')
                         );
        
        $fieldset->addField('website_id', 'hidden', array(
                    'name' => 'website_id'
              ));
        
        $fieldsetMapping = $form->addFieldset('projects_general_maping', array('legend' => $this->__('Mapping for %s', $websiteInfo->getName())));

        $this->_addElementTypes($fieldsetMapping);
        
        $fieldsetMapping->addType('store_view_label','Smartling_Connector_Block_Adminhtml_Projects_Edit_Form_Element_StoreViewLabel');

        
        $storeValues = Mage::helper('connector/data')->getStores($website_id);
        
        $localesData = Mage::registry('current_projects_locales');
        
        foreach ($storeValues as $storeId => $storeName) {
            
            $values = null;
            if(isset($localesData[$storeId])) {
                $values = $localesData[$storeId];
            }
            
            $fieldsetMapping->addField('store_view_labels[' . $storeId . ']', 'store_view_label', array(
                'label'         => $storeName,
                'name'          => 'store_view_labels',
                'required'      => false,
                'values'     => $values,
                'bold'      =>  true,
                'store_id' => $storeId,
                'after_element_html' => '<p class="note" style="background-position:17px; padding-left:27px"><span>' . Mage::helper('connector')->__('enter smartling locale code here') . '</span></p>',
                'class' => 'sl-required-entry-multy'
            ));
        }
        
        $formData = array();
        
        if ($project = Mage::registry('current_projects')) {
            $formData = $project->getData();
        }
        
        $formData['website_id']= $website_id;
        
        $form->setValues($formData);
        
        return parent::_prepareForm();
    }

}
