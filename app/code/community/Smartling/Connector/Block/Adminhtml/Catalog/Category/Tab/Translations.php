<?php

/**
 * Description of Tranlsations
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Catalog_Category_Tab_Translations 
    extends Mage_Adminhtml_Block_Widget_Form
        implements Mage_Adminhtml_Block_Widget_Tab_Interface 
{
    
    protected $_category;

    public function __construct()
    {
        if (!Mage::helper('connector')->showTranslationTab()){
            return;
        }
        parent::__construct();
        $this->setShowGlobalIcon(true);
        
    }

    public function getCategory()
    {
        if (!$this->_category) {
            $this->_category = Mage::registry('category');
        }
        return $this->_category;
    }
    
    protected function _prepareForm(){                      
        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('edit')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }
        
        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('translations_');
        
        $fieldset = $form->addFieldset('smartling_translation', array(
            'legend'=>Mage::helper('connector')->__('Smartling Translation')
                ));
        
        $fieldset->addField('translation_is_active', 'select', array(
            'label'     => Mage::helper('connector')->__('Enable Smartling Translations'),
            'title'     => Mage::helper('connector')->__('Enable Smartling Translations'),
            'name'      => 'general[translation_is_active]',
            'required'  => true,
            'options'   => array(
                '1' => Mage::helper('connector')->__('Enabled'),
                '0' => Mage::helper('connector')->__('Disabled'),
            ),
            'disabled'  => $isElementDisabled,
        ));
        
        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $field = $fieldset->addField('translate_store_id', 'multiselect', array(
                'name'      => 'general[translate_stores][]',
                'label'     => Mage::helper('cms')->__('Store View'),
                'title'     => Mage::helper('cms')->__('Store View'),
                'required'  => false,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, false),
                'disabled'  => $isElementDisabled,
            ));
            $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
            $field->setRenderer($renderer);
        }
        else {
            $fieldset->addField('translate_store_id', 'hidden', array(
                'name'      => 'general[translate_stores][]',
                'value'     => Mage::app()->getStore(true)->getId()
            ));
            $this->getCategory()->setStoreId(Mage::app()->getStore(true)->getId());
        }       

        $form->addValues($this->getCategory()->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
    
    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('connector')->__('Smartling Translations');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        Mage::helper('connector')->showTranslationTab();
        return Mage::helper('connector')->__('Smartling Translations');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return Mage::helper('connector')->showTranslationTab();        
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/category/' . $action);
    }
}
