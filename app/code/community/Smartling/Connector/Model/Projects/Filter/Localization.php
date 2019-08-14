<?php

/**
 * Filter projects by products relations
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Projects_Filter_Localization
    extends Smartling_Connector_Model_Projects_Filter
{
    
    /**
     * 
     * @param int $website_id
     * @return array
     */
    public function getAvailableProjects($website_id) {
        
        $resource = Mage::getModel('core/resource');
        $storeViewTableName = $resource->getTableName('core_store');
        $storeGroupTableName = $resource->getTableName('core_store_group');
        $localizationFilesIndexTableName = $resource->getTableName('connector/localization_files_index');
        
        $collection = Mage::getModel('connector/projects')->getCollection();
        $collection->addFieldToFilter('active', 1);
        
        $collection->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns(array('name', 'row_id' => 'id', 'website_id'))
                ->joinLeft( array('pl' => $resource->getTableName('connector/projects_locales')), 'main_table.id = pl.parent_id')
                ->joinInner(array('cs' => $storeViewTableName),
                                        'pl.store_id = cs.store_id',
                                        array())
                ->joinInner(array('csg' => $storeGroupTableName),
                                        'cs.group_id = csg.default_store_id',
                                        array())
                ->joinInner(array('cs2' => $storeViewTableName),
                                        'csg.default_store_id = cs2.store_id',
                                        array())
                ->joinInner(array('lfi' => $localizationFilesIndexTableName),
                                        'cs2.store_localization_dir = lfi.dir_name',
                                        array())
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
