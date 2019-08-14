<?php

/**
 * Description of Content
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Content 
    extends Mage_Core_Model_Abstract
{
    
    /**
     * status completed
     */
    const CONTENT_STATUS_COMPLETED = 'completed';
    
    /**
     * status in progress
     */
    const CONTENT_STATUS_PROCESS = 'in progress';
    
    /**
     * status new
     */
    const CONTENT_STATUS_NEW = 'new';
    
    /**
     * set resource model
     * 
     */
    protected function _construct() {
        $this->_init('connector/content');
    }
    
    /**
     * 
     * @return string
     */
    public function getContentModelClass(){
        $type = $this->getType();
        $typesModel = Mage::getModel('connector/content_types')->load($type);
        if (!$typesModel->getId()){
            Mage::getSingleton('admin/session')->addError(
                    Mage::helper('connector')->__("Content Type does not exists")
                    );
            return;
        }
        return $typesModel->getModel();
    }
    
    /**
     * 
     * @param int | null $contentTypeId
     * @return Smartling_Connector_Model_Resource_Content_Collection
     */
    public function getAddedItems($contentTypeId = null){
        $collection = $this->getCollection()->addFieldToSelect('origin_content_id');                                           
        
        if (!is_null($contentTypeId)){
            $collection->addFieldToFilter('type', array('eq' => (int) $contentTypeId));
        }
        
        $collection->getSelect()->group('origin_content_id');        
        return $collection;
    }
    
    /**
     * 
     * @return Smartling_Connector_Model_Resource_Content_Collection
     */
    public function getNewItems($project_id = 0, $content_id = 0){
        $status = Smartling_Connector_Model_Content::CONTENT_STATUS_NEW;  
        $resource = Mage::getSingleton('core/resource');
        $collection = $this->getCollection()
                           ->addFieldToFilter('status', array('eq' => $status));
        
        if($project_id) {
            $collection->addFieldToFilter('project_id', array('eq' => $project_id));
        }
        
        if($content_id) {
           $collection->addFieldToFilter('content_id', array('eq' => $content_id));
        }
        
        $collection->getSelect()
                   ->reset(Zend_Db_Select::COLUMNS)
                   ->columns(array('content_id', 'type', 'origin_content_id', 'store_id', 'source_store_id', 'filename', 'project_id'))
                   ->joinLeft(array('t' => $resource->getTableName('connector/content_types')),
                                           'main_table.type = t.type_id',
                                           array('model'))
                   ->joinInner(array('p' => $resource->getTableName('connector/projects')),
                                           'main_table.project_id = p.id',
                                           array('project_code' => 'project_id', 'api_key' => 'key', 'retrieval_type')
                                               )
                   ->joinInner(array('pl' => $resource->getTableName('connector/projects_locales')),
                                           'main_table.project_id = pl.parent_id and main_table.store_id = pl.store_id',
                                           array(
                                               'locales' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT pl.locale_code SEPARATOR ",")')
                                               ))
                   ->group('main_table.project_id')
                   ->group('main_table.origin_content_id');
        

        return $collection;
    }
    
    
    /**
     * Collection items which must be downloaded in singledownload Action
     * 
     * @param int $contentId
     * @param int $contentType
     * @return Smartling_Connector_Model_Resource_Content_Collection
     */
    public function prepareDownloadItems($contentId, $contentType) {
        $collection = $this->getCollection()
                           ->addFieldToFilter('origin_content_id', array('eq' => (int) $contentId))
                           ->addFieldToFilter('type', array('eq' => (int) $contentType))
                           ->addFieldToFilter('status', array('neq' => self::CONTENT_STATUS_NEW));
        return $collection;
    }
    
    /**
     * 
     * @param int $origin_content_id
     * @param int $project_id
     * @param int $store_id
     * @return Smartling_Connector_Model_Resource_Content_Collection
     */
    public function getCollectionByUniqueKeys($origin_content_id, $project_id, $store_id) {
        $collection = $this->getCollection()
                   ->addFieldTofilter('project_id', array('eq' => $project_id))
                   ->addFieldTofilter('origin_content_id', array('eq' => $origin_content_id))
                   ->addFieldTofilter('store_id', array('eq' => $store_id));
        return $collection;
    }
    
    /**
     * Item is ready wnen return empty array
     * @return array
     */
    public function checkReadyForDownload($status, $locale_name) {
        $errors = array();
        
        if($status == Smartling_Connector_Model_Content::CONTENT_STATUS_NEW) {
            $errors[] = Mage::helper('connector')->__("Sorry, the entity hasn't being translated yet. Locale [%s]", $locale_name);
        } elseif($status == Smartling_Connector_Model_Content::CONTENT_STATUS_COMPLETED) {
            $errors[] = Mage::helper('connector')->__("Sorry, the entity hasn already translated and applied. Locale [%s]", $locale_name);
        }
        
        return $errors;
    }
}
