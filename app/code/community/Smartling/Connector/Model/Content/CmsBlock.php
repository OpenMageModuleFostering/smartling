<?php

/**
 * Description of CmsBlock
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Content_CmsBlock 
    extends Smartling_Connector_Model_Content_Abstract
        implements Smartling_Connector_Model_Content_Interface
{
    
    const CONTENT_TYPE = 'cmsBlock';
    
    /**
     *
     * @var string 
     */
    protected $_fileTypeModel;
    
    /**
     *
     * @var string 
     */
    protected $_title;
    
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
    protected $_attributesRegistryPrefix = 'cms_block_columns_';
    
    public function __construct() {        
        $this->_fileTypeModel = 'connector/types_general';
    }    
    
    /**
     * Creates xml content for translation
     * Also defines filename uri for upload content to Smartling via API
     * Defines content title for smartling translation table
     * 
     * @param Mage_Cms_Model_Block|int $category $category
     * @param int $project_id Profile ID
     * @param int $sourceStoreId Source locale
     * @return string
     */
    public function createTranslateContent($cmsBlock, $project_id, $sourceStoreId) {
        if (is_numeric($cmsBlock)) {
            $cmsBlock = $this->getContentTypeEntityModel()
                             ->setStoreId($sourceStoreId)
                             ->load((int)$cmsBlock);
        }
        
        if (!$cmsBlock->getId()){
            Mage::getModel('adminhtml/session')->addError(
                    Mage::helper('connector')->__('Block does not exists')
                    );
            return;
        }
        
        $content = Mage::getModel($this->_fileTypeModel);
        $content->setContentGroupAttribute(array('block_id' => $cmsBlock->getId()));
        
        //retrieve field from registry to translate
        $flatColumnsCollection =  Mage::registry($this->_attributesRegistryPrefix);
        
        if(($flatColumnsCollection instanceof Smartling_Connector_Model_Resource_Translate_Fields_List_Block_Collection) == false ) {
            //retrieve field to translate
            $flatColumnsCollection = $this->getAttributes();

            if (!$flatColumnsCollection->getSize()) {
                $this->_getAdminSession()->addError(
                        Mage::helper('connector')->__("CMS Block columns weren't selected")
                        );

                Mage::helper('connector')->log("CMS Block columns weren't selected", Zend_log::ERR);
                return '-1';
            } else {
                Mage::register($this->_attributesRegistryPrefix, $flatColumnsCollection);
            }
        }
        
        //create smartling type content
        foreach ($flatColumnsCollection as $node_name => $row) {
            
            $nodeOptions = array('attribute' => $node_name, 
                                 'type' => 'textarea');
            
            $content->setContent($cmsBlock->getData($node_name), $nodeOptions, 'htmlcontent');
        }
                
        //create full xml content for Api
        $translateContent = $content->createContentFile();                
        
        $this->_fileUri = $this->formatFileUri('cms_block', $cmsBlock->getIdentifier(), $project_id);
        $this->_title = $cmsBlock->getTitle();
        
        return $translateContent;
    }
    
    /**
     * 
     * @return \Smartling_Connector_Model_Resource_Translate_Fields_List_Block_Collection 
     */
    public function getAttributes() {
    
        $collection = Mage::getResourceModel('connector/translate_fields_list_block_collection');
        
        // include items which attached to translate list
        $collection->includeWithValue('is_attached', 1);
        
        return $collection;
    }
    
    /**
     * 
     * @return string
     */
    public function getFullFileUri() {
        return Mage::getBaseDir('media') . DS . $this->getBaseFileDir() 
                                         . DS . $this->_fileUri;
    }
        
    /**
     * 
     * @param array $translatedContent
     * @param int $contentId
     * @param array $storeId
     * @param Smartling_Connector_Model_Content $contentModel
     * @return bool
     */
    public function createContent($translatedContent, $contentId, $storeId){
        /** @var Mage_Cms_Model_Block */
        $block = Mage::getModel('cms/block')->load($contentId);
        if (!$block->getId()){
            return false;
        }
        
        $data = $block->getData();
        unset($data['block_id']);
        unset($data['creation_time']);
        $blockData = array_merge($data, $translatedContent);
        
        /** @var Mage_Cms_Model_Block */
        $newBlock = Mage::getModel('cms/block');             
        $newBlock->setData($blockData);       
        $newBlock->setStores($storeId);
        
        // Check if destination store grouped by content with other stores
        if($this->countLinkedStores($block->getIdentifier(), $storeId)) {
            $this->unlinkContentFromStore($contentId, $storeId);
        }
       
        if ($id = $this->checkIdentifier($block->getIdentifier(), $storeId)){
            $newBlock->setId($id);           
        }
                  
        try{
            $newBlock->save();
            return $newBlock;
        } catch (Exception $e) {
            
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('connector')->__($e->getMessage())
                    );
            
            Mage::helper('connector')->log($e->getMessage(), Zend_log::ERR);
            
           return false;
       }
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
        return Mage::getModel('cms/block');
    }
    
    /**
     * 
     * @param int $block_id
     * @param int $store_id
     */
    protected function unlinkContentFromStore($block_id, $store_id) {
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('write');
        
        $tableName = Mage::getModel('cms/block')
                ->getResource()
                ->getTable('block_store');
        
        $conditions = array();
        $conditions[] = $connection->quoteInto('block_id=?', $block_id);
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
                array('cps' => $resource->getTableName('cms/block_store')),
                'cp.block_id = cps.block_id ' 
                 . ' and cp.identifier = "' . addslashes($identifier) . '" '
                 . ' and cps.store_id != ' . (int)$store_id,
                array())
            ->joinInner(
                array('cps2' => $resource->getTableName('cms/block_store')),
                'cps.block_id = cps2.block_id and cps2.store_id = ' . (int)$store_id,
                array());

        $select->reset(Zend_Db_Select::COLUMNS)
            ->columns('cp.block_id')
            ->order('cps.store_id DESC');
        
        return count($resource->getConnection('read')->fetchAll($select));
    }    
    
    /**
     * 
     * @param string $identifier
     * @param string $store_id
     * @return int
     */
    public function checkIdentifier($identifier, $store_id) {
        $table = $this->getContentTypeEntityModel()->getResource()
                                                   ->getMainTable();
        $resource = Mage::getSingleton('core/resource');
        $select = $resource->getConnection('read')->select()
            ->from(array('cp' => $table))
            ->join(
                array('cps' => $resource->getTableName('cms/block_store')),
                'cp.block_id = cps.block_id',
                array())
            ->where('cp.identifier = ?', $identifier)
            ->where('cps.store_id = ?', $store_id);

        $select->reset(Zend_Db_Select::COLUMNS)
            ->columns('cp.block_id')
            ->order('cps.store_id DESC')
            ->limit(1);       
        return $resource->getConnection('read')->fetchOne($select);
    }
    
     /**
     * 
     * @return boolean
     */
    public function saveNames() {
        return parent::saveFlatNames('cms/block_store');
    }
}
