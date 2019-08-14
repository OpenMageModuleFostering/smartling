<?php

/**
 * Description of Content
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Projects
    extends Mage_Core_Model_Abstract
{
    
    /**
     * List of loaded projects
     * @var array
     */
    protected $_projectsData;
    
    /**
     * set resource model
     * 
     */
    protected function _construct() {
        $this->_init('connector/projects');
    }
    
    /**
     * Return all projects which have locale mapping
     * @param boolean $active
     * @return \Smartling_Connector_Model_Resource_Projects_Collection
     */
    public function getProjectsList($includeInactive = false) {
        
        $resource = Mage::getModel('core/resource');
        
        $collection = $this->getCollection();
        
        // include only active projects
        if($includeInactive == false) {
            $collection->addFieldToFilter('active' , 1);
        }
        
        $collection->getSelect()
                ->joinInner( array('pl' => $resource->getTableName('connector/projects_locales')), 
                             'main_table.id = pl.parent_id', 
                             array("parent_id", "store_id", "locale_code")
                           )
                ->group('main_table.id');
        
        return $collection;
    }
    
    
    /**
     * Return all projects locales
     * @return array
     */
    public function getProjectsLocales($contentTypeId = '', $content_id = '', $website_ids = array()) {
        
        $projects = array();
        
        $resource = Mage::getModel('core/resource');
        
        $collection = $this->getCollection();
        $collection->addFieldToFilter('active', 1);
        if(sizeof($website_ids)) {
            $collection->addFieldToFilter('website_id', array('in' => $website_ids));
        }
        
        $collection->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns(array('name', 'row_id' => 'id', 'website_id'))
                ->joinLeft( array('pl' => $resource->getTableName('connector/projects_locales')), 'main_table.id = pl.parent_id');
           
        if($contentTypeId && $content_id) { 
            $collection->getSelect()
                    ->joinLeft( array('c' => $resource->getTableName('connector/translate_content')), 
                                              'pl.store_id = c.store_id' 
                                           . ' and c.type = ' . (int)$contentTypeId
                                           . ' and c.origin_content_id = ' . (int)$content_id,
                                array('percent' => 'c.percent')
                              );
        }
        
        $collection->getSelect()
                     ->group('pl.id')
                     ->order('main_table.id');
        
        foreach ($collection as $project) {
            
            if(!isset($projects[$project->getRowId()])) {
                $projects[$project->getRowId()] = array(
                                                    'id' => $project->getId(),
                                                    'name' => $project->getName(),
                                                    'website_id' => $project->getWebsiteId()
                                                    );
            }
            
            $localeName = $this->getLocaleTitle($project->getStoreId());
            
            $projects[$project->getRowId()]['locales'][$project->getStoreId()] = 
                                               array(
                                                     'id' => $project->getStoreId(),
                                                     'project_identity' => $project->getId(),
                                                     'name' => $localeName,
                                                     'percent' => $project->getPercent()
                                                    );
            
        }
        
        return $projects;
    }
    
    /**
     * 
     * @param int $storeId
     * @return string
     */
    public function getLocaleTitle($storeId) {
        $localeName = Mage::app()->getStore($storeId)->getFrontendName()
                       . ' (' . Mage::app()->getStore($storeId)->getName() . ')';
        
        return $localeName;
    }

    /**
     * Get project data
     * @param int $project_id
     * @return boolean|\Smartling_Connector_Model_Resource_Projects_Collection
     */
    public function getProjectData($project_id) {
        if(isset($this->_projectsData[$project_id])) {
            return $this->_projectsData[$project_id];
        }
        
        $collection = $this->getCollection();
        $collection->addFieldToFilter('id' , $project_id);
        
        if($collection->getSize()) {
            $this->_projectsData[$project_id] = $collection;
            return $collection;
        } else {
            return false;
        }
    }
}
