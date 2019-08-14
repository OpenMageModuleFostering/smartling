<?php

/**
 * Description of Collection
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Resource_Content_Collection 
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    
    protected function _construct() {
        $this->_init('connector/content');
    }
    
    /**
     * 
     * @param int $content_id
     * @return \Smartling_Connector_Model_Resource_Content_Collection
     */
    public function addIndentifyToFilter($content_id)
    {
        $this->addFieldToFilter('content_id', $content_id);
    
        return $this;
    }
    
    /**
     * 
     * @param int $content_id
     * @return \Smartling_Connector_Model_Resource_Content_Collection
     */
    public function addUniqueByParams($origin_content_id, $project_id, $storeId)
    {
        $this->addFilterToMap('origin_content_id', 'main_table.origin_content_id')
                              ->addFilterToMap('project_id', 'main_table.project_id')
                              ->addFilterToMap('store_id', 'main_table.store_id');
            
        $this->addFieldToFilter('origin_content_id', $origin_content_id)
                              ->addFieldToFilter('project_id', $project_id)
                              ->addFieldToFilter('store_id', $storeId);
    
        return $this;
    }
    
    /**
     * Join additional tables to collection data
     * @return \Smartling_Connector_Model_Resource_Content_Collection
     */
    public function joinAdditionalDetails()
    {
        $resource = Mage::getModel('core/resource');
        
        $this->getSelect()
                          ->joinInner(
                                array('p' => $resource->getTableName('connector/projects')), 
                                'main_table.project_id = p.id', 
                                array('project_id' => 'id', 
                                      'project_code' => 'project_id', 
                                      'api_key'=> 'key',
                                      'api_url', 'retrieval_type')
                               )
                          ->joinInner(
                                array('pl' => $resource->getTableName('connector/projects_locales')), 
                                'main_table.project_id = pl.parent_id and main_table.store_id = pl.store_id ', 
                                array('locale' => 'locale_code')
                               )
                          ->joinInner(
                                array('ct' => $resource->getTableName('connector/content_types')), 
                                'main_table.type = ct.type_id', 
                                array('model_class' => 'model')
                               );
        return $this;
    }
}
