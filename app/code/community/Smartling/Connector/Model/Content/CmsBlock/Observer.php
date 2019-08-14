<?php

/**
 * Description of Observer
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Content_CmsBlock_Observer 
{
   
    /**
     * upload content for translation if translation enabled
     * 
     * @param Varien_Event_Observer $cmspage
     */
    public function sendBlockContentToTranslator(Varien_Event_Observer $object){
        $modelObject = $object->getEvent()->getObject();
        if ($modelObject instanceof Mage_Cms_Model_Block){           
            if ($modelObject->getLocales() && is_array($modelObject->getLocales())){
                $locales = $modelObject->getData('locales'); 
                /** @var $contentModel Smartling_Connector_Model_Content_CmsBlock */ 
                $contentModel = Mage::getModel('connector/content_cmsBlock');
                /** @var $translator Smartling_Connector_Model_Translator */
                $translator = Mage::getModel('connector/translator');
                
                /**
                 * create and save xml content for smartling translations
                 */
                //$content = $contentModel->createTranslateContent($modelObject);
                $type = Mage::helper('connector')
                          ->findTypeIdByTypeName(Smartling_Connector_Model_Content_CmsBlock::CONTENT_TYPE);
                $processData = array(
                        'type'              => $type,
                        'origin_content_id' => $modelObject->getId(),
                        'content_title'     => $modelObject->getTitle(),                                      
                        );                
                             
//                 if ($content != ""){   
//                    for ($i = 0; $i < sizeof ($locales); $i++) {
//                        $translator->uploadContent($contentModel, $locales[$i], $processData, $content);
//                    }   
//                 } else {
//                     Mage::getSingleton('adminhtml/session')->addError(
//                         Mage::helper('connector')->__('Content does not exists')
//                         );
//                 }
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
    }
}
