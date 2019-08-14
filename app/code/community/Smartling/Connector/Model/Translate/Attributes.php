<?php

/**
 * Description of Content
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Translate_Attributes
    extends Mage_Core_Model_Abstract
{
    
    /**
     * set resource model
     * 
     */
    protected function _construct() {
        $this->_init('connector/translate_attributes');
    }
   
    /**
     * 
     * @param int $columnValue
     * @param string $columnName
     * @param int $entity_type_id
     * @return type
     */
    public function load($columnValue, $columnName = 'id', $entity_type_id = 4) {
        
        $collection = $this->getCollection()
                ->addFieldToFilter($columnName, $columnValue)
                ->addFieldToFilter('entity_type_id', $entity_type_id);
                
        return $collection->getFirstItem();
    }
}
