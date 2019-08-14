<?php

/**
 * Description of CallUpload
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Service_Observer 
{
    public function prepareAttributesOptions(Varien_Event_Observer $object) {
        
        $data = $object->getEvent()->getData();
       
        $cmsContentModel = $data['cms_content_model_instanse'];
        $item = $data['item'];
        $response = $data['response'];
                
        if($cmsContentModel instanceof Smartling_Connector_Model_Content_EavEntityInterface) {
            $translatedContent = $cmsContentModel->getTranslatedAttributesOptions($response);
            $attributesOptions = Mage::getSingleton('connector/content_attributes_options');

            foreach ($translatedContent as $optionValue) {
                $attributesBiulder = Mage::getModel('connector/content_attributes_builder');

                $attributesBiulder->setStoreId($item['store_id'])
                                  ->setOptionId($optionValue['option_id'])
                                  ->setValue($optionValue['value']);

                $params = $attributesBiulder->buildParameters();
                $attributesOptions->add($params);
            }
        }
    }
    
    /**
     * 
     * @param Varien_Event_Observer $object
     */
    public function updateTranslatedContentId(Varien_Event_Observer $object) {
        
        $data = $object->getEvent()->getData();
        $item = $data['item'];
        $newContent = $data['new_content'];
        
        if (is_object($newContent) && $newContent->getId()) {
            
            if (is_null($item->getTranslatedContentId($newContent->getId()))) {
                $item->setTranslatedContentId($newContent->getId());
            } 

            try {

                $logMessage = sprintf('Smartling saves the original xml file for entity type = %s, id = %s. Locale: %s. Project: %s', 
                        $item->getModelClass(),
                        $newContent->getId(),
                        $item['locale'],
                        $item['project_code']
                        );

                $item->save();
                Mage::helper('connector')->log($logMessage, Zend_log::DEBUG);
            } catch (Mage_Exception $e) {
                Mage::helper('connector')->log($e->getMessage(), Zend_log::ERR);
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
    }
    
    /**
     * Import attributes options
     * @param Varien_Event_Observer $object
     */
    public function importEavOptions(Varien_Event_Observer $object) {
        
        $data = $object->getEvent()->getData();
        $attributesOptions = $data['attributes_options_instanse'];
     
        $attributesOptions->run();
    }
    
    /**
     * Set compeleted status to translated entities
     * @param Varien_Event_Observer $object
     */
    public function updateContentStatuses(Varien_Event_Observer $object) {
        
        $contentModel = Mage::getModel('connector/content');
        $contentCollection = $contentModel->getCollection();
        
        $contentCollection->addFieldToFilter('percent', array('eq' => 100))
                          ->addFieldToFilter('status', array('neq' => $contentModel::CONTENT_STATUS_COMPLETED));
        
        foreach ($contentCollection as $item) {
            $item->setStatus($contentModel::CONTENT_STATUS_COMPLETED);
            $item->save();
        }
        
    }
    
}
