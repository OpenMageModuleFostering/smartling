<?php

/**
 * Description of Abstract
 *
 * @author Smartling
 */
abstract class Smartling_Connector_Model_Content_Abstract 
    extends Mage_Core_Model_Abstract
{
        
    /**
     *
     * @var string 
     */
    protected $_entityType;
    
    /**
     *
     * @var string 
     */
    protected $_fileTypeModel;
    
    /**
     *
     * @var string 
     */
    protected $_title = '';
    
    /**
     *
     * @var string 
     */
    protected $_fileUri;
    
    public function __construct() {        
        $this->_fileTypeModel = 'connector/types_xml';        
    }   
    
    abstract public function saveNames();
            
    /**
     * 
     * @return string|bool
     */
    public function getFileUri() {
        if(strlen($this->_fileUri)) {
            return $this->_fileUri . ".xml";
        } else {
            return false;
        }
    }
    
    /**
     * 
     * @param array $storeIds
     * @return array
     */
    public function defineTranslateLocales($storeIds){
        $locales = Mage::helper('connector')->getLocaleMap();
        $translateLocales = array();
        //$stores = (explode(",", $storeIds));
        if (count($storeIds) > 1){
            for ($i = 0; $i < count($storeIds); $i++){
                $translateLocales[] = $locales[$storeIds[$i]];
            }
        } else {
            $translateLocales[] = $locales[$storeIds[0]];
        }            
        return $translateLocales;        
    }
    
    /**
     * 
     * @return string
     */
    public function getBaseFileDir(){
        return $this->_baseFileDir;
    }
    
    /**
     * 
     * @return Mage_Adminhtml_Model_Session
     */
    protected function _getAdminSession(){
        return Mage::getSingleton('adminhtml/session');
    } 
    
    /**
     * Return parsed response 
     * 
     * @param string $translatedContent
     * @return array
     */
    public function getTranslatedContent($translatedContent) {
        $content = Mage::getModel($this->_fileTypeModel);
        return $content->loadContent($translatedContent)->getAllData();
    }
    
    /**
     * Return parsed response for attributes options
     * 
     * @param string $translatedContent
     * @return array
     */
    public function getTranslatedAttributesOptions($translatedContent) {
        $content = Mage::getModel($this->_fileTypeModel);
        return $content->loadContent($translatedContent)->getOptionsValues();
    }
    
    /**
     * 
     * @param int $entityType
     * @param array $exclude
     * @return null | array
     */
    protected function _getAttributes($entityType, array $exclude){
        $trnaslateAttribs = array();         
        $attrCollection = Mage::helper('connector')
                                ->getAttributesForTranslation($entityType, $exclude);
        if ($attrCollection->getSize() < 1){
            return null;
        }
        
        foreach ($attrCollection as $_attribute){
            if ($_attribute->getData('is_html_allowed_on_front') == 1){
                $trnaslateAttribs['htmlcontent'][] = $_attribute->getAttributeCode();
            } else {
                $trnaslateAttribs['content'][] = $_attribute->getAttributeCode();
            }            
        }
        return $trnaslateAttribs;
    }
    
    /**
     * 
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract $attrCollection
     * @return array
     */
    protected function formatAttributes(Mage_Core_Model_Resource_Db_Collection_Abstract $attrCollection) {
        
        $translateAttributes = array();         
        
        if (!$attrCollection->getSize()){
            return array();
        }
        
        foreach ($attrCollection as $_attribute) {
            $areaKey = ($_attribute->getData('is_html_allowed_on_front') == 1)?'htmlcontent':'content';
            $translateAttributes[$areaKey][] = array('attribute' => $_attribute->getAttributeCode(), 
                                                     'type' => $_attribute->getFrontendInput());
        }
        
        return $translateAttributes;
    }
    
    /**
     * Creates the same content for other locales
     * 
     * @param array $translatedContent
     * @param int $originContentId
     * @param int $storeId
     * @param Smartling_Connector_Model_Content $contentModel
     * @return boolean|Mage_Core_Model_Abstract 
     */
    public function createDynamicContent($translatedContent, $originContentId, $storeId) {
        $contentTypeEntity = $this->getContentTypeEntityModel();
        $contentTypeEntity->setStoreId($storeId); // set store in origData
        $contentTypeEntity->load($originContentId);
        if (!$contentTypeEntity->getId()){
            return false;
        }
        
        $errors = '';
        
        if($contentTypeEntity instanceof Mage_Catalog_Model_Category) {
            $this->setDefaultAttributesValues($contentTypeEntity, $translatedContent);
        }
        
        foreach ($translatedContent as $attribute => $value) {
            $contentTypeEntity->setData($attribute, $value);
        }     
        
        $contentTypeEntity->setStoreId($storeId);
        $contentTypeEntity->setWebsiteIds(array( Mage::app()->getStore($storeId)->getWebsite()->getId() ));
        
        try {
            $contentTypeEntity->save();
        } catch (Exception $e){
            Mage::getSingleton('adminhtml/session')->addError(
                  Mage::helper('connector')->__($e->getMessage())
                 );
            $errors = $e->getMessage();
        }
        
        if ($errors) {
            Mage::helper('connector')->log($errors, Zend_log::ERR);
            return false;
        }
        
        return $contentTypeEntity;
    } 
    
     /**
     * Check default value usage fact
     * 
     * @param Mage_Eav_Model_Entity_Attribute $dataObject
     * @param mixed $attributeModel
     * @return boolean
     */
     protected function usedDefault($dataObject, $attributeModel)
    {
        
        $attributeCode = $attributeModel->getAttributeCode();
        $value = $dataObject->getData($attributeCode);
        
        $defaultValue = $dataObject->getAttributeDefaultValue($attributeCode);

        if (!$dataObject->getExistsStoreValueFlag($attributeCode)) {
            return true;
        } else if ($value == $defaultValue &&
            $dataObject->getStoreId() != Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID
        ) {
            return false;
        }
        if ($defaultValue === false && !$attributeModel->getIsRequired() && $value) {
            return false;
        }
        return $defaultValue === false;
    }

    /**
     * Overwrite 'use default value' checkbox is nesesary
     * @param Mage_Catalog_Model_Abstract $contentTypeEntity
     * @param array $translatedContent
     */
    protected function setDefaultAttributesValues($contentTypeEntity, $translatedContent) {
        
        $attributeCodes = array_keys($contentTypeEntity->getData());
        foreach ($attributeCodes as $attributeCode) {
            $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode($this->_entityType, $attributeCode);
            
            $isGlobalPriceScope = false;
            
            if ($attributeCode == 'price') {
                $priceScope = Mage::getStoreConfig('catalog/price/scope');
                if ($priceScope == 0) {
                    $isGlobalPriceScope = true;
                }
            }
            
            if(!sizeof($attributeModel->getData()) ||
                  $attributeModel->getData('is_global') == Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL ||
                  $isGlobalPriceScope  
            ) {
                continue;
            }
            
            $usedDefault = $this->usedDefault($contentTypeEntity, $attributeModel);
            
            // check if default value uses and translated content is not empty
            if($usedDefault && !array_key_exists($attributeModel->getAttributeCode(), $translatedContent)) {
                $contentTypeEntity->setData($attributeCode, false);
            }
        }
    }

     /**
     * define entity type Id by entity code
     * 
     * @return bool | int
     */
    public function getEntityTypeId(){
        $entityType = Mage::getModel('eav/entity_type')->loadByCode($this->_entityType);
        if (!$entityType->getId()){
            Mage::helper('connector')
                    ->log("Entity type " . $this->_entityType . " undefined", Zend_log::ERR);
            return false;
        }
        return $entityType->getId();
    } 
    
    /**
     * Used to define content title while adding new content for translation
     * Holds in smartling content table  
     * 
     * @return string
     */
    public function getContentTitle() {
        return $this->_title;
    }
    
    /**
     * 
     * @param string $filename
     * @param int $item_id
     * @param int $project_id
     * @return string
     */
    public function formatFileUri($filename, $item_id, $project_id) {
        $fileUri = $filename . "_" . $item_id . '_project-' . $project_id;
        return $fileUri;
    }
    
    /**
     * Fill empty title name fields
     * @return boolean
     */
    protected function saveEavNames() {
        
        try {
            $resource = Mage::getSingleton('core/resource');
            $_adapter = $resource->getConnection('core_write');

            $adminStoreId = Mage_Core_Model_App::ADMIN_STORE_ID;

            $model = $this->getContentTypeEntityModel();

            $entity = $model->getResource();

            $entityTableName = $resource->getTableName($model->getResourceName());

            $attribute = Mage::getSingleton('eav/config')->getCollectionAttribute($entity->getType(), 'name');
            $attributeId = $attribute->getId();
            $backendTableName = $attribute->getBackendTable();

            $contentTableName = $resource->getTableName('connector/translate_content');

            $updateNamesQuery = "UPDATE {$contentTableName} c "
                                    . " JOIN {$entityTableName} AS `e` ON c.origin_content_id = e.entity_id "
                                        . " AND c.content_title = '' " 
                                    . " JOIN {$backendTableName} AS `at_name` ON " 
                                        . " (`at_name`.`entity_id` = `e`.`entity_id`) " 
                                        . " AND (`at_name`.`attribute_id` = '{$attributeId}') " 
                                        . " AND (`at_name`.`store_id` = {$adminStoreId})  
                                SET c.content_title = at_name.value";

            $_adapter->query($updateNamesQuery);
        } catch (Exception $ex) {
            Mage::helper('connector')->log("Current entity names (EAV Model) weren't updated: " . $ex->getMessage(), Zend_log::ERR);
            return false;
        }
        
        return true;
    }
    
    /**
     * Fill empty title name fields
     * @param string $tableStoreNameKey
     * @param string $titleFiledName
     * @return boolean
     */
    protected function saveFlatNames($tableStoreNameKey, $titleFiledName = 'title') {
        
        try {
            $resource = Mage::getSingleton('core/resource');
            $_adapter = $resource->getConnection('core_write');

            $model = $this->getContentTypeEntityModel();

            $entityTableName = $resource->getTableName($model->getResourceName());
            $IdFieldName = $model->getIdFieldName();
            
            $contentTableName = $resource->getTableName('connector/translate_content');
            $entityStoreTableName = $resource->getTableName($tableStoreNameKey);

            $updateNamesQuery = $this->getFlatNamesQuery($contentTableName, $entityTableName, $entityStoreTableName, $IdFieldName, $titleFiledName, 'c.source_store_id');
            $_adapter->query($updateNamesQuery);

            $updateNamesQueryDefaultStoreGrouped = $this->getFlatNamesQueryGrouped($contentTableName, $entityTableName, $entityStoreTableName, $IdFieldName, $titleFiledName);
            $_adapter->query($updateNamesQueryDefaultStoreGrouped);
            
            $updateNamesQueryDefaultStore = $this->getFlatNamesQuery($contentTableName, $entityTableName, $entityStoreTableName, $IdFieldName, $titleFiledName);
            $_adapter->query($updateNamesQueryDefaultStore);            
            
        } catch (Exception $ex) {
            Mage::helper('connector')->log("Current entity names (Flat Model) weren't updated: " . $ex->getMessage(), Zend_log::ERR);
            return false;
        }
        
        return true;
    }
    
    /**
     * Get query template to update title at flat table
     * @param type $contentTableName
     * @param type $entityTableName
     * @param type $IdFieldName
     * @param type $titleFiledName
     * @param type $sourceStoreField
     */
    private function getFlatNamesQuery($contentTableName, $entityTableName, $entityStoreTableName, $IdFieldName, $titleFiledName, $sourceStoreField = 0) {
        
        $updateNamesQuery = "UPDATE {$contentTableName} c "
                                    . " JOIN {$entityTableName} AS `e` ON c.content_title = '' " 
                                        . " AND c.origin_content_id = e." . $IdFieldName . " "
                                    . " JOIN {$entityStoreTableName} AS `e2s` ON " 
                                        . " (`e2s`.`{$IdFieldName}` = `e`.`{$IdFieldName}`) " 
                                        . " AND (`e2s`.`store_id` = {$sourceStoreField})"
                                    . " SET c.content_title = e.`{$titleFiledName}`";
        return $updateNamesQuery;                                
    }
    
    /**
     * Get query template to update title at flat table. Query is cross content store view and default store group
     * @param type $contentTableName
     * @param type $entityTableName
     * @param type $IdFieldName
     * @param type $titleFiledName
     */
    private function getFlatNamesQueryGrouped($contentTableName, $entityTableName, $entityStoreTableName, $IdFieldName, $titleFiledName) {
        
        $updateNamesQuery = "UPDATE {$contentTableName} c "
                                    . " JOIN {$entityTableName} AS `e` ON c.content_title = '' " 
                                        . " AND c.origin_content_id = e." . $IdFieldName . " "
                                    . " JOIN {$entityStoreTableName} AS `e2s` ON " 
                                        . " (`e2s`.`{$IdFieldName}` = `e`.`{$IdFieldName}`) " 
                                    . "JOIN core_store_group cs ON cs.default_store_id = e2s.store_id "
                                        . "AND `e2s`.`store_id` = cs.default_store_id"
                                    . " SET c.content_title = e.`{$titleFiledName}`";
        return $updateNamesQuery;                                
    }
    
    //

    /**
     * Retrieve stores collection with default store
     *
     * @return Mage_Core_Model_Mysql4_Store_Collection
     */
    public function getStores()
    {
        $stores = $this->getData('stores');
        if (is_null($stores)) {
            $stores = Mage::getModel('core/store')
                ->getResourceCollection()
                ->setLoadDefault(true)
                ->load();
            $this->setData('stores', $stores);
        }
        return $stores;
    }
    
    /**
     * 
     * @param string $content
     * @param string $fileUri
     * @param array $item
     * @return bool
     */
    public function uploadContent($content, $fileUri, $item) {
        
        /** @var $translator Smartling_Connector_Model_Translator */
        $translator = Mage::getModel('connector/translator', 
                                            array('apiKey' => $item['api_key'], 
                                                  'projectId' => $item['project_code'],
                                                  'project_id' => $item['project_id'],
                                                  'locales' => $item['locales']
                                                )
                                         );
        $translator->setFileType(Smartling_Connector_Model_Translator::FILE_TYPE_XML);
        
        $response = $translator->uploadTranslateContent($content, $fileUri);
        
        Mage::helper('connector')->log($response, Zend_log::INFO);
        
        return $translator->isSuccessResponse($response);
    }
    
    /**
     * Get system directory path
     * 
     * @return boolean
     */
    public function getSystemDirectoryPath() {
        return Mage::getBaseDir('var') . '/smartling/localization_files';
    }
    
    
    
}
