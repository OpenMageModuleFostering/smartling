<?php

/**
 * Filter projects by categories relations
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Projects_Filter_Category
    extends Smartling_Connector_Model_Projects_Filter
{
    /**
     * 
     * @param int $store_group_id
     * @return array
     */
    public function getAvailableProjects($store_group_id) {
        
        $resource = Mage::getModel('core/resource');
        $collection = Mage::getModel('core/store_group')->getCollection();
        $collection->addFieldToFilter('main_table.group_id', $store_group_id);
        
        $collection->getSelect()
                ->joinInner( array('s' => $resource->getTableName('core/store')), 
                                        'main_table.group_id = ' . (int)$store_group_id 
                                    . ' and s.group_id = main_table.group_id')
                ->joinInner( array('pl' => $resource->getTableName('connector/projects_locales')), 
                                        'pl.store_id = s.store_id', array('id'))
                ->joinInner( array('p' => $resource->getTableName('connector/projects')), 
                                        'p.id = pl.parent_id')
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns(array('p.name', 
                                'row_id' => 'p.id', 
                                'id' => 'pl.id', 
                                'p.website_id', 
                                's.store_id' 
                            ));
        
        $options = $this->getProjectList($collection);
        
        return $options;
    }
    
    /**
     * 
     * @return string
     */
    public function getMessageOnEmptyRequest() {
        return Mage::helper('connector')->__('Please select store to see list of available profiles');
    }
    
}
