<?php

/**
 * Description of Observer
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Content_Product_Observer 
{
    
    /**
     * upload content for translation if translation enabled
     * 
     * @param Varien_Event_Observer $object
     */
    public function sendProductContentToTranslator(Varien_Event_Observer $object){
        $product = $object->getEvent()->getProduct();         
        if (is_array($product->getData('locales'))) {
            $locales = $product->getData('locales');
        //if ($product->getId() && $product->getTranslationIsActive()){
            $contentModel = Mage::getModel('connector/content_product');
            $translator = Mage::getModel('connector/translator');
            
            //create and save xml content for smartling translations
            //$content = $contentModel->createTranslateContent($product);        
            
            $processData = array(
                    'type'              => Mage::helper('connector')
                                            ->findTypeIdByTypeName(Smartling_Connector_Model_Content_Product::CONTENT_TYPE),
                    'origin_content_id' => $product->getId(),
                    'content_title'     => $product->getName(),                                 
                    ); 
                
//             if ($content != ""){
//                 for ($i = 0; $i < sizeof ($locales); $i++) {
//                    $translator->uploadContent($contentModel, $locales[$i], $processData, $content);  
//                }
//             } 
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
            $product = $object->getEvent()->getProduct();
            $product->setData('locales', $locales);
        }
    }
}
