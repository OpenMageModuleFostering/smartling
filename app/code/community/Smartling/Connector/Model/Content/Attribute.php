<?php

/**
 * Description of Attibute
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Content_Attribute 
    extends Smartling_Connector_Model_Content_Abstract
        implements Smartling_Connector_Model_Content_Interface
{
    
    const CONTENT_TYPE = 'attribute';
    
    /**
     * define entity type 
     * 
     * @var string 
     */
    protected $_entityType = 'catalog_attribute';
    
    /**
     *
     * @var string 
     */
    protected $_fileTypeModel;
    
    /**
     *
     * @var string 
     */
    protected $_fileUri;
    
    /**
     * Magento entity type ID
     * @var int 
     */
    protected $_entityTypeId;
    
    /**
     * Define attributes registry key prefix
     * 
     * @var string
     */
    protected $_attributesRegistryPrefix = 'eav_attributes_';
    
    public function __construct() {
        parent::__construct();
        $this->_fileTypeModel = 'connector/types_general';
        $this->_entityTypeId = Mage::getModel('eav/entity')->setType(Mage_Catalog_Model_Product::ENTITY)->getTypeId();
    }
    
    /**
     * Creates the same content for other locales
     * 
     * @param array $translatedContent
     * @param int $id
     * @param array $storeId
     * @param Smartling_Connector_Model_Content $contentModel
     */
    public function createContent($data, $id, $storeId){
        
        if ($data) {
            /** @var $session Mage_Admin_Model_Session */
            $session = Mage::getSingleton('adminhtml/session');
            /* @var $model Mage_Catalog_Model_Entity_Attribute */
            $model = Mage::getModel('catalog/resource_eav_attribute');
            /* @var $helper Mage_Catalog_Helper_Product */
            $helper = Mage::helper('catalog/product');

            if ($id) {
                $model->load($id);

                if (!$model->getId()) {
                    $session->addError(
                        Mage::helper('catalog')->__('This Attribute no longer exists'));
                    return false;
                }

                // entity type check
                if ($model->getEntityTypeId() != $this->_entityTypeId) {
                    $session->addError(
                        Mage::helper('catalog')->__('This attribute cannot be updated.'));
                    $session->setAttributeData($data);
                    return false;
                }

                $data['attribute_code'] = $model->getAttributeCode();
                $data['is_user_defined'] = $model->getIsUserDefined();
                $data['frontend_input'] = $model->getFrontendInput();
            } else {
                $data['source_model'] = $helper->getAttributeSourceModelByInputType($data['frontend_input']);
                $data['backend_model'] = $helper->getAttributeBackendModelByInputType($data['frontend_input']);
            }

            if (!isset($data['is_configurable'])) {
                $data['is_configurable'] = 0;
            }
            if (!isset($data['is_filterable'])) {
                $data['is_filterable'] = 0;
            }
            if (!isset($data['is_filterable_in_search'])) {
                $data['is_filterable_in_search'] = 0;
            }

            if (is_null($model->getIsUserDefined()) || $model->getIsUserDefined() != 0) {
                $data['backend_type'] = $model->getBackendTypeByInput($data['frontend_input']);
            }

            $defaultValueField = $model->getDefaultValueByInput($data['frontend_input']);

            if(!isset($data['apply_to'])) {
                $data['apply_to'] = array();
            }
            
            // Add data to options for all stores
            $this->parseData($model, $data, $storeId);
            
            $model->addData($data);

            try {
                $model->save();
                $session->addSuccess(
                    Mage::helper('catalog')->__('The attribute has been saved.'));

                /**
                 * Clear translation cache because attribute labels are stored in translation
                 */
                Mage::app()->cleanCache(array(Mage_Core_Model_Translate::CACHE_TAG));
                $session->setAttributeData(false);
                return true;
            } catch (Exception $e) {
                $session->addError($e->getMessage());
                $session->setAttributeData($data);
                return false;
            }
        } else {
            $errorMessage = Mage::helper('connector')->__('Attribute data has not been specified.');
            $session->addError($errorMessage);
            return false;
        }
    
    }
    
    /**
     * Parse received data and merge with original one
     * 
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $model
     * @param array $data The data received from smartling
     * @param type $destinationStoreId
     * @return boolean
     */
    protected function parseData(Mage_Catalog_Model_Resource_Eav_Attribute $model, &$data, $destinationStoreId) {
     
        /** @var $helperCatalog Mage_Catalog_Helper_Data */
        $helperCatalog = Mage::helper('catalog');
        
        $exist_data = $model->getData();
        
        // Attribute labels
        foreach ($data['frontend_label'] as & $value) {
            if ($value) {
                $value = $helperCatalog->stripTags($value);
            }
        }

        // Merge original and received data
        $data = array_replace_recursive($exist_data, $data);
        
        $data['default'] = array();
        
        if(isset($exist_data['default_value']) && strlen($exist_data['default_value'])) {
            $data['default'] = explode(',', $exist_data['default_value']);
        }

        if(is_null($model->getSource())) {
            return true; 
        }
        
        // Parse attributes options
        Mage::helper('connector')->mergeAttributesOptions($model, $data, $destinationStoreId);
        
        return true;
    }
    
    /**
     * Creates xml content for translation
     * Also defines filename uri for upload content to Smartling via API
     * Defines content title for smartling translation table
     * 
     * @param Mage_Catalog_Model_Resource_Attribute|int $attribute
     * @param int $project_id Profile ID
     * @param int $sourceStoreId Source locale
     * @return string
     */
    public function createTranslateContent($attribute, $project_id, $sourceStoreId) {
        
        if (is_numeric($attribute)) {
            $attribute = $this->getContentTypeEntityModel()
                            ->setStoreId($sourceStoreId)
                            ->load($attribute);
        }
        
        $label = $attribute->getStoreLabel();
        
        $optionsAttributeHead = array('attribute' => 'frontend_label',
                                      'type' => 'text');
        
        $content = Mage::getModel($this->_fileTypeModel);
        $content->setContentGroupAttribute(array('attribute_id' => $attribute->getId()));
        $content->setContent($label, $optionsAttributeHead, 'htmlcontent');
        
        if ($attribute->usesSource()) {
            
            $optionsAttributeOptionsHead = array('attribute' => 'options_values',
                                                 'type' => $attribute->getFrontendInput());
            
            $contentValue = array();
            $options = $attribute->getSource()->getAllOptions(false);
            
            if(is_array($options)) {
                foreach($options as $option) {
                    $contentValue[$option['value']] = $option['label'];
                }
            } else {
                Mage::helper('connector')->log(
                            Mage::helper('connector')
                                ->__('%s attribute options is not an array', 
                                        $attribute->getAttributeCode()),
                                              Zend_Log::DEBUG);
            }
            
            if(sizeof($contentValue)) {
                switch ($attribute->getFrontendInput()) {
                    case 'select':
                    case 'multiselect':
                        $content->setContent($contentValue, $optionsAttributeOptionsHead, 'list');
                    break;
                    default:
                        Mage::helper('connector')->log(
                            Mage::helper('connector')
                                ->__('%s attribute has unexpected type. multiselect and select supported. %s received', 
                                        $attribute->getAttributeCode(),
                                        $attribute->getFrontendInput()), 
                                                     Zend_Log::DEBUG);
                    break;
                }
            }
        }
        
        //create full xml content for Api
        $translateContent = $content->createContentFile();
        
        $filename = ($attribute->getAttributeCode()) ? $attribute->getAttributeCode() . "_attribute_id" : 'attribute';
        
        $this->_fileUri = $this->formatFileUri($filename, $attribute->getId(), $project_id);
        $this->_title = $label;
        
        return $translateContent;
        
    }
    
    /**
     * 
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    public function getContentTypeEntityModel() {
        return Mage::getModel('catalog/resource_eav_attribute');
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
     * @return boolean
     */
    public function saveNames() {
        
        try {
            $resource = Mage::getSingleton('core/resource');
            $_adapter = $resource->getConnection('write');

            $entityTableName = $resource->getTableName('eav_attribute');
            
            $contentTableName = $resource->getTableName('connector/translate_content');
            
            $updateNamesQuery = "UPDATE {$contentTableName} c "
                                    . " JOIN {$entityTableName} AS `e` ON c.content_title = '' " 
                                        . " AND c.origin_content_id = e.attribute_id "
                                    . " SET c.content_title = e.frontend_label";
            
            $_adapter->query($updateNamesQuery);
        } catch (Exception $ex) {
            Mage::helper('connector')->log("Attributes names weren't updated: " . $ex->getMessage(), Zend_log::ERR);
            return false;
        }
        
        return true;
    }
    
    /**
     * Return parsed response 
     * 
     * @param string $translatedContent
     * @return array
     */
    public function getTranslatedContent($translatedContent) {
        $content = Mage::getModel($this->_fileTypeModel);
        $attributeData = $content->loadContent($translatedContent)->getAllData();
        
        $optionsValues = $content->loadContent($translatedContent)->getOptionsValues();
        if(sizeof($optionsValues)) {
            $attributeData['option'] = $optionsValues;
        }
        
        return $attributeData;
    }
}
