<?php

/**
 * Description of Cms
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Content_CmsPage
    extends Smartling_Connector_Model_Content_Abstract
        implements Smartling_Connector_Model_Content_Interface
{
     
    /**
     * define content type name
     */
    const CONTENT_TYPE = 'cmsPage';
    
    /**
     *
     * @var string 
     */
    protected $_fileTypeModel;
    
    /**
     *
     * @var string 
     */
    protected $_baseFileDir;
    
    /**
     *
     * @var string 
     */
    protected $_fileUri;
    
    /**
     * Define attributes registry key prefix
     * 
     * @var string
     */
    protected $_attributesRegistryPrefix = 'cms_page_columns_';
        
    public function __construct() {        
        $this->_fileTypeModel = 'connector/types_general';
    }
    
    /**
     * Creates xml content for translation
     * Also defines filename uri for upload content to Smartling via API
     * Defines content title for smartling translation table
     * 
     * @param Mage_Cms_Model_Page|int $category $category
     * @param int $project_id Profile ID
     * @param int $sourceStoreId Source locale
     * @return string
     */
    public function createTranslateContent($cmsPage, $project_id, $sourceStoreId) {
        
        if (is_numeric($cmsPage)) {
            $cmsPage = $this->getContentTypeEntityModel()
                            ->setStoreId($sourceStoreId)
                            ->load($cmsPage);
        }
        
        if (!$cmsPage->getId()){
            Mage::getModel('adminhtml/session')->addError(
                    Mage::helper('connector')->__('Page does not exists')
                    );
            return;
        }
        
        //retrieve field from registry to translate
        $flatColumnsCollection =  Mage::registry($this->_attributesRegistryPrefix);
        
        if(($flatColumnsCollection instanceof Smartling_Connector_Model_Resource_Translate_Fields_List_Page_Collection) == false ) {
            //retrieve field to translate
            $flatColumnsCollection = $this->getAttributes();

            if (!$flatColumnsCollection->getSize()) {
                $this->_getAdminSession()->addError(
                        Mage::helper('connector')->__("CMS Page columns weren't selected")
                        );

                Mage::helper('connector')->log("CMS Page columns weren't selected", Zend_log::ERR);
                return '-1';
            } else {
                Mage::register($this->_attributesRegistryPrefix, $flatColumnsCollection);
            }
        }
        
        $content = Mage::getModel($this->_fileTypeModel);
        $content->setContentGroupAttribute(array('page_id' => $cmsPage->getId()));
        
        //create smartling type content
        foreach ($flatColumnsCollection as $node_name => $row) {
            
            $nodeOptions = array('attribute' => $node_name, 
                                 'type' => 'textarea');
            
            $content->setContent($cmsPage->getData($node_name), $nodeOptions, 'htmlcontent');
        }
        
        // create full xml content for APi
        $translateContent = $content->createContentFile();    
        
        $this->_fileUri = $this->formatFileUri('cms_page', $cmsPage->getIdentifier(), $project_id);
        $this->_title = $cmsPage->getTitle();
        
        return $translateContent;        
    }    
        
    
    /**
     * 
     * @param array $translatedContent
     * @param int $pageId
     * @param int $storeId
     * @param Smartling_Connector_Model_Content $contentModel
     * @return bool
     */
    public function createContent($translatedContent, $pageId, $storeId){
        /** @var $page Mage_Cms_Model_Page */
        $page = Mage::getModel('cms/page')->load($pageId);        
        
        if (!$page->getId()){
            return false;
        }
        
        $data = $page->getData();
        unset($data['page_id']);
        unset($data['creation_time']);
        $pageData = array_merge($data, $translatedContent);
        /** @var $newPage Mage_Cms_Model_Page */
        $newPage = Mage::getModel('cms/page');             
        $newPage->setData($pageData);
        $newPage->setStores($storeId);
        
        // Check if destination store grouped by content with other stores
        if($this->countLinkedStores($page->getIdentifier(), $storeId)) {
            $this->unlinkContentFromStore($pageId, $storeId);
        }
        
        if ($id = $this->checkIdentifier($page->getIdentifier(), $storeId)) { 
            $newPage->setId($id);
        }
        
        try {
            $newPage->save();             
            return $newPage;
        } catch (Exception $e) {
            
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('connector')->__($e->getMessage()));
            
            Mage::helper('connector')->log($e->getMessage(), Zend_log::ERR);
            
            return false;
        }
    }
    
    /**
     * 
     * @param int $page_id
     * @param int $store_id
     */
    protected function unlinkContentFromStore($page_id, $store_id) {
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('write');
        
        $tableName = Mage::getModel('cms/page')
                ->getResource()
                ->getTable('page_store');
        
        $conditions = array();
        $conditions[] = $connection->quoteInto('page_id=?', $page_id);
        $conditions[] = $connection->quoteInto('store_id=?', $store_id);
        
        $connection->delete($tableName, $conditions);
    }

    /**
     * Count linked stores to specified content except destination store
     * 
     * @param string $identifier
     * @param string $store_id
     * @return int
     */    
    protected function countLinkedStores($identifier, $store_id) {
        $table = $this->getContentTypeEntityModel()->getResource()
                                                   ->getMainTable();
        $resource = Mage::getSingleton('core/resource');
        $select = $resource->getConnection('read')->select()
            ->from(array('cp' => $table))
            ->joinInner(
                array('cps' => $resource->getTableName('cms/page_store')),
                'cp.page_id = cps.page_id ' 
                 . ' and cp.identifier = "' . addslashes($identifier) . '" '
                 . ' and cps.store_id != ' . (int)$store_id,
                array())
            ->joinInner(
                array('cps2' => $resource->getTableName('cms/page_store')),
                'cps.page_id = cps2.page_id and cps2.store_id = ' . (int)$store_id,
                array());

        $select->reset(Zend_Db_Select::COLUMNS)
            ->columns('cp.page_id')
            ->order('cps.store_id DESC');
        
        return count($resource->getConnection('read')->fetchAll($select));
    }

    /**
     * 
     * @return \Smartling_Connector_Model_Resource_Translate_Fields_List_Page_Collection 
     */
    public function getAttributes() {
    
        $collection = Mage::getResourceModel('connector/translate_fields_list_page_collection');
        
        // include items which attached to translate list
        $collection->includeWithValue('is_attached', 1);
        
        return $collection;
    }
    
    /**
     * 
     * @return string
     */
    public function getContentTypeCode(){
        return static::CONTENT_TYPE;
    }
    
    /**
     * 
     * @return Mage_Catalog_Model_Category
     */
    public function getContentTypeEntityModel(){
        return Mage::getModel('cms/page');
    }
    
    /**
     * 
     * @param string $identifier
     * @param string $store
     * @return int
     */
    public function checkIdentifier($identifier, $store) {
        $table = $this->getContentTypeEntityModel()->getResource()
                                                   ->getMainTable();
        $resource = Mage::getSingleton('core/resource');
        $select = $resource->getConnection('read')->select()
            ->from(array('cp' => $table))
            ->join(
                array('cps' => $resource->getTableName('cms/page_store')),
                'cp.page_id = cps.page_id',
                array())
            ->where('cp.identifier = ?', $identifier)
            ->where('cps.store_id IN (?)', $store);

        $select->reset(Zend_Db_Select::COLUMNS)
            ->columns('cp.page_id')
            ->order('cps.store_id DESC')
            ->limit(1);    
        return $resource->getConnection('read')->fetchOne($select);
    }
    
     /**
     * 
     * @return boolean
     */
    public function saveNames() {
        return parent::saveFlatNames('cms/page_store');
    }
}
