<?php

/**
 * Description of Abstract
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Content_Attributes_Options 
    extends Mage_Core_Model_Abstract
{
    
    /**
     * 
     * List of values for import
     * @var array 
     */
    protected $_list;
    
    /**
     * Add unique param to array
     * @param array $params
     */
    public function add(array $params) {
        $this->_list[$params['option_id']][$params['store_id']] = $params;
    }
    
    /**
     * 
     * @return boolean
     */
    public function run() {
        
        if(!sizeof($this->_list)) {
            return false;
        }
        
        $incUpdated = 0;
        
        $resource = Mage::getModel('core/resource');
        $adapter = $resource->getConnection('core_write');
        
        $attributeOptionTable = $resource->getTableName('eav_attribute_option_value');
                
        foreach ($this->_list as $storeParams) {
            foreach ($storeParams as $params) {
                $query = "SELECT count(*) as total FROM " . $attributeOptionTable . " WHERE option_id = :option_id AND store_id = :store_id and value = :value";
                $binds = array(
                        'option_id' => $params['option_id'],
                        'store_id' => $params['store_id'],
                        'value' => $params['value']
                );
                
                $total = $adapter->fetchOne($query, $binds);
                if(!$total) {
                    $adapter->insertMultiple($attributeOptionTable, $binds);
                    $incUpdated++;
                }
            }
        }
        
        if($incUpdated) {
            Mage::helper('connector')->log("{$incUpdated} eav attribute option values have been updated", Zend_log::INFO);
        }
        
        return (boolean)$incUpdated;
    }
    
}