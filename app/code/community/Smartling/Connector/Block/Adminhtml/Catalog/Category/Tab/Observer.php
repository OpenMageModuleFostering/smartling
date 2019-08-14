<?php

/**
 * Description of Observer
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Catalog_Category_Tab_Observer 
{
    
    public function addTranslationTab(Varien_Event_Observer $object){
        $tabs = $object->getEvent()->getTabs();
        $categoryId = Mage::app()->getRequest()->getParam('id');
        $blockClass = 'translateExisting';
        
        if (Mage::helper('connector')->showTranslationTab()){
            $tabs->addTab('translator', array(
                'label'     => Mage::helper('connector')->__('Smartling Translator'),
                'content'   => Mage::app()->getLayout()->createBlock(
                    "connector/adminhtml_catalog_category_tab_{$blockClass}",
                    'category.translations.grid'
                )->toHtml(),
            ));
        }        
    }
    
}
