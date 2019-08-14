<?php

/**
 * Description of Observer
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Content_Category_Observer
{
    
    /**
     * upload content for translation if translation enabled
     * 
     * @param Varien_Event_Observer $object
     */
    public function sendCategoryContentToTranslator(Varien_Event_Observer $object){
        $category = $object->getEvent()->getCategory();                
        if (is_array($category->getData('locales'))) { 
            $locales = $category->getData('locales');        
            $contentModel = Mage::getModel('connector/content_category');
            $translator = Mage::getModel('connector/translator');
            
            //create and save xml content for smartling translations
            //$content = $contentModel->createTranslateContent($category);
            
            $processData = array(
                    'type'              => Mage::helper('connector')
                                            ->findTypeIdByTypeName(Smartling_Connector_Model_Content_Category::CONTENT_TYPE),
                    'origin_content_id' => $category->getId(),
                    'content_title'     => $category->getName(),                                   
                    ); 
            for ($i = 0; $i < sizeof($locales); $i++) {
                //$translator->uploadContent($contentModel, $locales[$i], $processData, $content);                    
                $result = Mage::getResourceModel('connector/content')
                            ->addSingleItem($locales[$i], $processData);
                if ($result && $result !== 0) {
                    $message = Mage::helper('connector')
                            ->__("New item added in translation queue for locale %s", $locales[$i]);
                    Mage::getSingleton('adminhtml/session')->addSuccess($message);
                } elseif ($result == 0) {
                    $message = Mage::helper('connector')
                            ->__("Item already added in translation queue for locale %s", $locales[$i]);
                    Mage::getSingleton('adminhtml/session')->addSuccess($message);
                } else {
                    $errors = "Unable to add Item to translation queue for locale {$locales[$i]}";
                    Mage::getSingleton('adminhtml/session')->addError($message);
                }
            }
//            if ($content != ""){    
//                for ($i = 0; $i < sizeof ($locales); $i++) {
//                    $translator->uploadContent($contentModel, $locales[$i], $processData, $content); 
//                }
//             } else {
//                 Mage::getSingleton('adminhtml/session')->addError(
//                         Mage::helper('connector')->__('Content does not exists')
//                         );
//             }       
        }
    }
    
    /**
     * Add locales params to product for sending product content to Smartling
     * 
     * 
     * @param Varien_Event_Observer $object
     */
    public function addTranslationOptions (Varien_Event_Observer $object) {        
        $request = $object->getEvent()->getRequest();
        if ($locales = $request->getParam('locales')) {
            $category = $object->getEvent()->getCategory();
            $category->setData('locales', $locales);
        }
    }
}
