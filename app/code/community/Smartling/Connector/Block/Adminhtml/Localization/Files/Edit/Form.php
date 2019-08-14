<?php

/**
 * Description of Form
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Localization_Files_Edit_Form 
    extends Mage_Adminhtml_Block_System_Store_Edit_Form
{
          
    protected function _prepareForm()
    {
        parent::_prepareForm();
        
        $widgetForm = Mage_Adminhtml_Block_Widget_Form::_prepareForm();
        
        if(Mage::registry('store_type') != 'store') {
            return $widgetForm;
        }
        
        $storeModel = Mage::registry('store_data');
        
        $form = $this->getForm();

        $fieldset = $form->getElement('store_fieldset');
        
        $fieldset->addField('store_localization_dir', 'select', array(
                'name'      => 'store[store_localization_dir]',
                'label'     => Mage::helper('connector')->__('Localization Directory'),
                'value'     => $storeModel->getStoreLocalizationDir(),
                'options'   => Mage::helper('connector')->getLocalizationDirectoriesList(true),
                'required'  => true,
                'disabled'  => $storeModel->isReadOnly(),
            ));
        
        return $widgetForm;
    }
}
