<?php

/**
 * Description of Form
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Cms_Block_Edit_Form 
    extends Mage_Adminhtml_Block_Cms_Block_Edit_Form
{
          
    protected function _prepareForm()
    {
        parent::_prepareForm();

        $form = $this->getForm();
        
        $fieldsetTranslation = $form->addFieldset('smartling', 
                                                        array('legend'=> Mage::helper('connector')->__('Smartling Translations'), 
                                                              'class' => 'fieldset-wide')
                                                 );
        
        $fieldsetTranslation->addType('translations', 'Smartling_Connector_Block_Adminhtml_Cms_Block_Edit_Translations');
        $field = $fieldsetTranslation->addField('translations', 'translations', array(
            'label'     => Mage::helper('connector')->__('Translations'),
            'title'     => Mage::helper('connector')->__('Translations')
        ));
        
        return Mage_Adminhtml_Block_Widget_Form::_prepareForm();
    }
}
