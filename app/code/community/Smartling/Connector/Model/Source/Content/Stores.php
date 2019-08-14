<?php

/**
 * Description of Stores
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Source_Content_Stores     
{
    
    /**
     * 
     * @return array
     */
    public function toOptionArray() {
        $stores = Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, false);
        $defaultStore = Mage::helper('connector')->getSourceStoreView();
        
        if (sizeof($stores) == 0) {
            return array();
        }
        
        $translateStores = array();
        
        foreach ($stores as &$store) {
            if (is_array($store['value'])) {
                for ($i = 0; $i < sizeof($store['value']); $i++) {
                    if ($store['value'][$i]['value'] == $defaultStore) {
                        unset ($store['value'][$i]);
                    }
                }
            }
        }
        
        return $stores;
    }
}