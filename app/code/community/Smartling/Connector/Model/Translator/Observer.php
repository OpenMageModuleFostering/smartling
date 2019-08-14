<?php

/**
 * Description of CallUpload
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Translator_Observer 
{
    /**
     * Set compeleted status to translated entities
     * @param Varien_Event_Observer $object
     */
    public function fillSourceLocale(Varien_Event_Observer $object) {
        
        try {
            
            $resource = Mage::getSingleton('core/resource');
            $_adapter = $resource->getConnection('write');

            $contentTableName = $resource->getTableName('connector/translate_content');
            $storeViewTableName = $resource->getTableName('core_store');
            $storeGroupTableName = $resource->getTableName('core_store_group');

            $updateQuery = "UPDATE {$contentTableName} sc "
                                        . " inner join {$storeViewTableName} cs on sc.store_id = cs.store_id and sc.source_store_id is NULL "
                                        . " inner join {$storeGroupTableName} csg on cs.group_id = csg.group_id "
                                . " SET sc.source_store_id = csg.default_store_id";

            $_adapter->query($updateQuery);
            
        } catch (Exception $ex) {
            Mage::helper('connector')->log("Fill source locale observer. " . $ex->getMessage(), Zend_log::ERR);
            return false;
        }
        
        return true;
        
    }
    
    /**
     * Set names to content items
     * @param Varien_Event_Observer $object
     */
    public function fillTitles(Varien_Event_Observer $object) {
        $contentModel = $object->getEvent()->getData('model_instance');
        if($contentModel instanceof Smartling_Connector_Model_Content_Interface) {
            $contentModel->saveNames();
        }
    }
}
