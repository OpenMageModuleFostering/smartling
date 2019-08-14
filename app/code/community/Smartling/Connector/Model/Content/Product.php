<?php

/**
 * Description of Product
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Content_Product 
    extends Smartling_Connector_Model_Content_Abstract
        implements Smartling_Connector_Model_Content_Interface, 
                   Smartling_Connector_Model_Content_EavEntityInterface 
{
    
    const CONTENT_TYPE = 'product';
    
    /**
     * define entity type 
     * 
     * @var string 
     */
    protected $_entityType = 'catalog_product';

    /**
     * Define attributes registry key prefix
     * 
     * @var string
     */
    protected $_attributesRegistryPrefix = 'products_attributes_';
    
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
    
    public function __construct() {
        parent::__construct();
        $this->_fileTypeModel = 'connector/types_general';
    }

     /**
     * Creates the same content for other locales
     * 
     * @param array $translatedContent
     * @param int $originContentId
     * @param int $storeId
     * @param Smartling_Connector_Model_Content $contentModel
     */
    public function createContent($translatedContent, $originContentId, $storeId) {
        
        if(!is_array($translatedContent)) {
            return false;
        }
        
        foreach ($translatedContent as $key => $value) {
            if(is_array($value)) {
                $translatedContent[$key] = implode(',', $value);
            }
        }
        
        $action = Mage::getModel('catalog/resource_product_action');
        $action->updateAttributes(array($originContentId), $translatedContent, $storeId);
    }
    
    /**
     * Creates xml content for translation
     * Also defines filename uri for upload content to Smartling via API
     * Defines content title for smartling translation table
     * 
     * @param Mage_Catalog_Model_Product|int $category $category
     * @param int $project_id Profile ID
     * @param int $sourceStoreId Source locale
     * @return string
     */
    public function createTranslateContent($product, $project_id, $sourceStoreId) {
        
        if (is_numeric($product)) {
            $product = $this->getContentTypeEntityModel()
                            ->setStoreId($sourceStoreId)
                            ->load($product);
        }
        
        $attributesKeyObject =  $this->_attributesRegistryPrefix . $product->getAttributeSetId();
        
        $attributes =  Mage::registry($attributesKeyObject);
        
        if(!is_array($attributes)) {
        
            $attributesCollection = $this->getAttributes($product->getAttributeSetId());

            if (!$attributesCollection->getSize()) {
                $this->_getAdminSession()->addError(
                        Mage::helper('connector')->__("product attributes weren't  selected")
                        );
                
                Mage::helper('connector')->log("Attributes weren't selected", Zend_log::ERR);
                
                return '-1';
            } else {
                $attributes = $this->formatAttributes($attributesCollection);
                Mage::register($attributesKeyObject, $attributes);
            }
        }
        
        /**
         * @TODO group entities at Smartling_Connector_ServiceController by entity type 
         * and load collection. createTranslateContent method should use collection item.
         */
        
        $productsCollection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('*')
                ->addIdFilter($product->getId());
        
        $product = $productsCollection->getFirstItem();
        
        $content = Mage::getModel($this->_fileTypeModel);
        $content->setContentGroupAttribute(array('product_id' => $product->getId()));
        
        //create smartling type content
        foreach ($this->_smartlingContentTypes as $type) {
            
            if (!empty($attributes[$type])) {
                
                $count = sizeof($attributes[$type]);
                for ($i = 0; $i < $count; $i++) {
                    $decoratorType = $type;

                    $contentValue = $product->getData($attributes[$type][$i]['attribute']);
                    if ($contentValue){
                        
                        switch ($attributes[$type][$i]['type']) {
                            case 'select':
                            case 'multiselect':
                                
                                $contentValueAttributes = $product->getAttributeText($attributes[$type][$i]['attribute']);
                                
                                // combine options ids with values
                                if(is_array($contentValueAttributes)) { // multiselect
                                    $attributeIds = explode(',',$contentValue);
                                    
                                    if(sizeof($attributeIds) == sizeof($contentValueAttributes)) {
                                        $contentValue = array_combine($attributeIds, $contentValueAttributes);
                                    } else {
                                        continue;
                                    } 
                                } else { // simple select
                                    $contentValue = array($contentValue => $contentValueAttributes);
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
                
        $filename = ($product->getUrlKey()) ? $product->getUrlKey() . '_prod_id' : 'product';        
        $this->_fileUri = $this->formatFileUri($filename, $product->getId(), $project_id);
        $this->_title = $product->getName();       
        
        return $translateContent;        
    }
    
    /**
     * 
     * @return type
     */
    public function getAttributes($attribute_set_id = 0) {
        
        $resource = Mage::getModel('core/resource');
        $systemAttributes = array_keys(Mage::getStoreConfig('connector/translate_attributes/catalog_product'));
        
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter()
            ->addFieldToFilter('is_global', Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE)
            ->addFieldToFilter('attribute_code', array('nin' => $systemAttributes));
        
        if($attribute_set_id) {
            $collection->setAttributeSetFilter($attribute_set_id);
        }
        
        $collection->getSelect()
                ->joinInner(
                            array('ia' => $resource->getTableName('connector/translate_attributes')), 
                                    'ia.attribute_id = main_table.attribute_id', array()
                        );
        
        return $collection;
    }

     /**
     * Return parsed response 
     * 
     * @param string $translatedContent
     */
    public function getTranslatedAttributesOptions($content) {
        return parent::getTranslatedAttributesOptions($content);
    }
     
    /**
     * 
     * @return Mage_Catalog_Model_Product
     */
    public function getContentTypeEntityModel() {
        return Mage::getModel('catalog/product');
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
        $productModel = Mage::getModel('catalog/product')->load($content_id);
        $this->_title = $productModel->getName();
    }
    
    /**
     * 
     * @return boolean
     */
    public function saveNames() {
        return parent::saveEavNames();
    }
}