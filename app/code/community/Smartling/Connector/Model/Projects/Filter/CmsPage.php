<?php

/**
 * Filter projects by products relations
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Projects_Filter_CmsPage
    extends Smartling_Connector_Model_Projects_Filter
{
    
    /**
     * 
     * @param int $website_id
     * @return array
     */
    public function getAvailableProjects($website_id) {
        
        $projects = array();
        
        $resource = Mage::getModel('core/resource');
        
        $collection = Mage::getModel('connector/projects')->getCollection();
        $collection->addFieldToFilter('active', 1);
        
        $collection->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns(array('name', 'row_id' => 'id', 'website_id'))
                ->joinLeft( array('pl' => $resource->getTableName('connector/projects_locales')), 'main_table.id = pl.parent_id');
                
        $collection->getSelect()
                     ->group('pl.id')
                     ->order('main_table.id');
        
        $projects = $this->getProjectList($collection);
        
        return $projects;
    }
    
    /**
     * 
     * @return string
     */
    public function getMessageOnEmptyRequest() {
        return '';
    }
}
