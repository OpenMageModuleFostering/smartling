<?php

/**
 * Description of Category
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Content_Category 
    extends Smartling_Connector_Model_Content_Abstract
        implements Smartling_Connector_Model_Content_Interface,
                   Smartling_Connector_Model_Content_EavEntityInterface 
{
    
    const CONTENT_TYPE = 'category';
    
    /**
     * define entity type 
     * 
     * @var string 
     */
    protected $_entityType = 'catalog_category';
    
    /**
     * Smartling content types
     * 
     * @var array
     */
    protected $_smartlingContentTypes = array('content', 'htmlcontent');
    
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
    protected $_attributesRegistryPrefix = 'category_attributes_';
    
    public function __construct() {
        parent::__construct();
        $this->_fileTypeModel = 'connector/types_general';
    }
    
    /**
     * Creates the same content for other locales
     * 
     * @param array $translatedContent
     * @param int $originContentId
     * @param array $storeId
     * @param Smartling_Connector_Model_Content $contentModel
     */
    public function createContent($translatedContent, $originContentId, $storeId){
        return parent::createDynamicContent($translatedContent, $originContentId, $storeId);
    }    
    
    /**
     * Creates xml content for translation
     * Also defines filename uri for upload content to Smartling via API
     * Defines content title for smartling translation table
     * 
     * @param Mage_Catalog_Model_Category|int $category $category
     * @param int $project_id Profile ID
     * @param int $sourceStoreId Source locale
     * @return string
     */
    public function createTranslateContent($category, $project_id, $sourceStoreId) {
        
        if (is_numeric($category)) {
            $category = $this->getContentTypeEntityModel()
                             ->setStoreId($sourceStoreId)
                             ->load($category);
        }
        
        $attributes =  Mage::registry($this->_attributesRegistryPrefix);
        
        if(!is_array($attributes)) {
            $attributesCollection = $this->getAttributes();

            if (!$attributesCollection->getSize()) {
                $this->_getAdminSession()->addError(
                        Mage::helper('connector')->__("Attributes weren't selected")
                        );

                Mage::helper('connector')->log("Category attributes weren't selected", Zend_log::ERR);
                return '-1';
            } else {
                $attributes = $this->formatAttributes($attributesCollection);
                Mage::register($this->_attributesRegistryPrefix, $attributes);
            }
        }
        
        
        
        $content = Mage::getModel($this->_fileTypeModel);
        $content->setContentGroupAttribute(array('category_id' => $category->getId()));
                
        //create smartling type content
        foreach ($this->_smartlingContentTypes as $type) {
            
            if (!empty($attributes[$type])) {
                
                $count = sizeof($attributes[$type]);
                for ($i = 0; $i < $count; $i++) {
                    $decoratorType = $type;

                    $contentValue = $category->getData($attributes[$type][$i]['attribute']);
                    
                    if ($contentValue){
                        
                        switch ($attributes[$type][$i]['type']) {
                            case 'select':
                                $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_category', $attributes[$type][$i]['attribute']);
                                $contentValueAttributes = $attribute->getSource()->getOptionText($category->getData($attributes[$type][$i]['attribute']));
                                $contentValue = array($contentValue => $contentValueAttributes);
                            break;    
                            case 'multiselect':
                                
                                $attributeIds = explode(',',$contentValue);
                                
                                $contentValue = array();
                                foreach ($attributeIds as $attributeId) {
                                    $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_category', $attributes[$type][$i]['attribute']);
                                    $contentValueAttributes = $attribute->getSource()->getOptionText($attributeId);
                                    $contentValue[$attributeId] = $contentValueAttributes;
                                }
                            break;
                            default:
                                if($type != 'htmlcontent' && strlen($contentValue) != strlen(strip_tags($contentValue))) {
                                    $decoratorType = 'htmlcontent';
                                }
                            break;
                        }
                        
                        if(is_array($contentValue)) {
                            $decoratorType = 'list';
                        }
                        
                        $content->setContent($contentValue, $attributes[$type][$i], $decoratorType);
                    }
                }
            }
        }
        
        //create full xml content for Api
        $translateContent = $content->createContentFile();
        
        $filename = ($category->getUrlKey()) ? $category->getUrlKey() . "_cat_id" : 'category';
        
        $this->_fileUri = $this->formatFileUri($filename, $category->getId(), $project_id);
        $this->_title = $category->getName();
        
        return $translateContent;
    }
    
     /**
     * 
     * @return \Mage_Catalog_Model_Resource_Category_Attribute_Collection
     */
    public function getAttributes() {
        
        $resource = Mage::getModel('core/resource');
        $systemAttributes = array_keys(Mage::getStoreConfig('connector/translate_attributes/catalog_category'));
        
        $collection = Mage::getResourceModel('catalog/category_attribute_collection')
            ->addFieldToFilter('is_global', Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE)
            ->addFieldToFilter('attribute_code', array('nin' => $systemAttributes));
        
        $collection->getSelect()
                ->joinInner(
                            array('ia' => $resource->getTableName('connector/translate_attributes')), 
                                    'ia.attribute_id = main_table.attribute_id', array()
                        );
        
        return $collection;
    }
    
    /**
     * 
     * @return Mage_Catalog_Model_Category
     */
    public function getContentTypeEntityModel(){
        return Mage::getModel('catalog/category');
    }    
    
    /**
     * 
     * @return string
     */
    public function getContentTypeCode(){
        return static::CONTENT_TYPE;
    }
    
    /**
     * @deprecated since version 0.2.2
     * @param int $content_id
     * @return string
     */
    public function setContentTitle($content_id) {
        $categoryModel = Mage::getModel('catalog/category')->load($content_id);
        $this->_title = $categoryModel->getName();
    }
    
    /**
     * 
     * @return boolean
     */
    public function saveNames() {
        return parent::saveEavNames();
    }
}
