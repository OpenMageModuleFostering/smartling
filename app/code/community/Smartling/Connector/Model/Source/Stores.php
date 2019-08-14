<?php

/**
 * Description of Website
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Source_Stores 
{
    
    /**
     * 
     * @param bool $includeDefault - Use "0" as first item
     * @return array
     */
    public function toOptionArray($includeDefault = false){
        
        $list = array();
        
        if($includeDefault) {
            $list[] = array(
                'value' => 0,
                'label' => Mage::helper('connector')->__('Please select store to see available profiles')
            );
        }
        
        $resource = Mage::getSingleton('core/resource');
        
        $stores = Mage::getResourceModel('core/store_group_collection');
        $stores->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                   ->columns(array('group_id', 'name'))
                   ->joinInner(array('s' => $resource->getTableName('core_store')),
                                           'main_table.group_id = s.group_id',
                                           array())
                   ->joinInner(array('pl' => $resource->getTableName('connector/projects_locales')),
                                           's.store_id = pl.store_id',
                                           array())
                   ->group('group_id');
        
        foreach ($stores as $store) {
            
            $list[] = array(
                'value' => $store->getGroupId(),
                'label' => $store->getName()
            );
        }
        
        return $list;
    }
}
