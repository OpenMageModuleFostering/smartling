<?php

/**
 * Description of Content
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Projects_Locales
    extends Mage_Core_Model_Abstract
{
    
    /**
     * set resource model
     * 
     */
    protected function _construct() {
        $this->_init('connector/projects_locales');
    }
    
    /**
     * 
     * @param int $store_id
     * @return string
     */
    public function getLocaleCodeByStoreId($store_id) {
        
        $collection = $this->getCollection()
                        ->addFieldToFilter('store_id', $store_id);
        
        $data = $collection->getFirstItem();
        
        return $data['locale_code'];
        
    }
    
    /**
     * 
     * @param mixed $stores_id
     * @return array|int
     */
    public function getLocaleCodes($stores_id) {
        
        $locale_code = '';
        
        $collection = $this->getCollection();
        if(!is_array($stores_id)) {
            
           $collection->addFieldToFilter('store_id', $stores_id);
           $data = $collection->getFirstItem();
           $locale_code = $data['locale_code'];
           
        } else {
            
           $locale_code = array();
           $collection->addFieldToFilter('store_id', array('in' => $stores_id));
           foreach ($collection as $item) {
               $locale_code[] = $item->getLocaleCode();
           }
        }
        
        return $locale_code;
        
    }

}
