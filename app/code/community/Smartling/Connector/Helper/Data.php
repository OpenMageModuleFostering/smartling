<?php

/**
 * Description of Data
 *
 * @author Itdelight
 */
class Smartling_Connector_Helper_Data 
    extends Mage_Core_Helper_Data
{   
    
    /**
     * 
     * @return string
     */
    public function getConfig($code, $configPath){
        $path = $code . "/" . $configPath;
        return Mage::app()->getStore()->getConfig($path);
    }
    
    /**
     * 
     * @param string $json
     * @return boolean
     */
    public function isJson($json){
        if (json_decode($json) !== null && is_object(json_decode($json))){
            return true;
        }
        return false;
    }
    
    /**
     * 
     * @param array $stores
     */
    public function prepareLocalesForSave($stores){
        if (!is_array($stores)){
            Mage::log ('uncorrect data for stores', null, 'SmartlingLog.log');
            return false;
        }
        
    }
    
    /**
     * 
     * @param string $identifier
     * @return string
     */
    public function getFileUri($identifier){
        $timetamp = time();
        $fileType = Mage::getStoreConfig('smartling/settings/file_type');
        return $identifier . "_" . $timetamp . "." . $fileType;
    }
    
    /**
     * 
     * @param string $localeCode
     * @return array
     */
    public function getStoresIdByLocale($localeCode) {
        $stores = array();
        $locales = $this->getLocaleMap();
        
        foreach ($locales as $key => $value){
            if ($value == $localeCode){
                $stores[] = $key;
            }
        }
        
        return $stores;
    }
    
    /**
     * 
     * @param array $storeIds
     * @return array
     */
    public function getStoresLocaleByIds($storeIds){
        $locales = $this->getLocaleMap();
        
        foreach ($locales as $storeId => $localeName){
            if (!in_array($storeId, $storeIds)){
                unset ($locales[$storeId]);
            }
        }
        
        return $locales;
    }
    
    /**
     * 
     * @param int $contentTypeId
     * @param int $content_id
     * @return array
     */
    public function getLocaleMap($contentTypeId = '', $content_id = ''){
        return Mage::getModel('connector/projects')->getProjectsLocales($contentTypeId, $content_id);
    }
    
    /**
     * 
     * @param string $type
     * @return int
     */
    public function findTypeIdByTypeName($type){
        $contentType = Mage::getModel('connector/content_types')->getCollection()
                                 ->addFieldToFilter('type_name', array('eq' => $type));                         
        if ($contentType->getSize() == 0){
            Mage::getSingleton('admin/session')->addError(
                    'Content type does not exists'
                    );
            return;
        }
        $type = $contentType->getFirstItem();        
        return $type->getTypeId();
    } 
   
    /**
     * 
     * @param string $type
     * @return string
     */
    public function findContentTypeModel($type){
        $contentModel = $this->_getContentType($type)->getModel();
        return $contentModel;
    }
    
    /**
     * 
     * @param string $type
     * @return Mage_Core_Model_Resource_Db_Collection
     */
    protected function _getContentType($type){
        $contentType = Mage::getModel('connector/content_types')->getCollection()
                                 ->addFieldToFilter('type_name', array('eq' => $type));                         
        if ($contentType->getSize() == 0){
            Mage::getSingleton('admin/session')->addError(
                    'Content type does not exists'
                    );
        }
        
        $type = $contentType->getFirstItem();
        return $type;
    }
    
    /**
     * 
     * @return int
     */
    public function getSubmitter(){
        $user = Mage::getSingleton('admin/session')->getUser();
        if(is_object($user)) {
            return $user->getId();
        } else {
            return 0;
        }
    }  
    
    /**
     * 
     * @param int $entityType
     * @param array $attributes
     * @return Mage_Eav_Model_Resource_Entity_Attribute_Collection
     * @deprecated since version 0.1.7
     */
    public function getAttributesForTranslation($entityType, $attributes){        
        $attributesCollection = Mage::getResourceModel('eav/entity_attribute_collection')
                                        ->setEntityTypeFilter($entityType)
                                        ->addFieldToFilter('main_table.attribute_id', array('in' => $attributes));
        
                                        
        return $attributesCollection;
    }
    
    /**
     * @deprecated since version 0.2.3
     * @return int
     */
    public function getDefaultStoreId(){        
        $storeId = Mage::app()->getDefaultStoreView()->getId();
        return $storeId;
    }
    
    /**
     * 
     * @return boolean
     */
    public function showTranslationTab(){
        $storeId = Mage::app()->getRequest()->getParam('store', 0);       
        if ($storeId && $storeId !== 0){
            return false;
        }
        return true;
    }
    
    /**
     * 
     * @param string $table you can use pattern with '/' or '_' symbols ex 'cms/page' or 'cms_page'
     * @return array
     */
    public function getFields($table){
        $allowedFieldsTypes = array('varchar', 'text', 'mediumtext');
        $fields = array();
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableName = $adapter->getTableName($table);
        $tableFields = $adapter->describeTable($tableName);
        foreach ($tableFields as $_field){
            if (in_array($_field['DATA_TYPE'], $allowedFieldsTypes)){
                $fields[] = $_field['COLUMN_NAME'];
            }
        }
        return $fields;
    }
    
    /**
     * Get entity ID by code
     * @param string $code
     * @return int
     */
    public function getEntityTypeId($code) {
        $entityType = Mage::getModel('eav/config')->getEntityType($code);
        $entityTypeId = $entityType->getEntityTypeId();
        
        return (int)$entityTypeId;
    }
    
    /**
     * Return all stores view exclude default
     * @param int $website_id
     * @param boolean $withDefault
     * @return array
     */
    public function getStores($website_id, $withDefault = false) {
        
        $options = array();
        $groupCollection = array();
        
        foreach (Mage::app()->getWebsites() as $website) {
            if($website->getId() != $website_id) continue;
            
            foreach ($website->getGroups() as $group) {
                $groupCollection[$group->getId()] = $group;
            }
        }
        
        $storeCollection = Mage::app()->getStores();
        $websiteCollection = Mage::app()->getWebsites();
        $nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');
        
        foreach ($websiteCollection as $website) {
            $websiteShow = false;
            foreach ($groupCollection as $group) {
                if ($website->getId() != $group->getWebsiteId()) {
                    continue;
                }
                $groupShow = false;
                foreach ($storeCollection as $store) {
                    if ($group->getId() != $store->getGroupId()) {
                        continue;
                    }
                        $options[$store->getId()] = 
                                                 $group->getName() . '<br>'
                                                    . str_repeat($nonEscapableNbspChar, 8) 
                                                        . '<b>' . $store->getName() . '</b>';
                }
                
            }
        }
        
        if(!$withDefault)  { 
            $defaultStoreId = self::getSourceStoreView();
            unset($options[$defaultStoreId]);
        }
        
        return $options;
    }
    
    /**
     * Return Id of default source view for Smartling module
     * @return int
     * @deprecated since version 0.2.1
     */
    public function getSourceStoreView($store_group_id) {
        
        $defaultStoreId = Mage::getStoreConfig('smartling/settings/source_store_view');
        
        if(!$defaultStoreId) {
            $defaultStoreId = Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStoreId();
        }
        
        return (int)$defaultStoreId;
    }
    
    /**
     * 
     * @return array
     */
    public function getRetrievalTypes() {
        return
            array(
                'published' => 'Published', 
                'pending' => 'Pending', 
                'pseudo' => 'Pseudo'
            );
    }
    
    /**
     * 
     * @return array
     */
    public function getDefaultRetrievalType() {
        return 'published';
    }
    
    /**
     * @param string $val
     * @return boolean
     */
    public function validateProjectId($val) {
        return (bool)(preg_match('/^[a-zA-Z0-9]*$/', $val));
    }
    
    /**
     * @param string $val
     * @return boolean
     */
    public function validateApiKey($val) {
        return (bool)(preg_match('/^\w{8}(?:-\w{4}){3}-\w{12}$/', $val));
    }
    
    /**
     * Log messages to specific file
     * 
     * @param string $message
     * @param int $level
     */
    public function log($message, $level = Zend_log::INFO) {
        
        $log_file = Mage::getStoreConfig('dev/smartling/log_file');
        $log_level = Mage::getStoreConfig('dev/smartling/log_level');
        $writeLog = false;
        
        if(!$log_file) {
            $log_file = 'smartling.log';
        }
        
        $levelErrors = array(
            Zend_log::EMERG,
            Zend_log::ALERT,
            Zend_log::CRIT,
            Zend_log::ERR,
            Zend_log::WARN
         );
        
        $levelInfo = array(
            Zend_log::NOTICE,
            Zend_log::INFO
         );
        
        // log errors only
        if(in_array($log_level, $levelErrors) && in_array($level, $levelErrors)) {
           $writeLog = true; 
        }
        
        // log info and errors
        if(in_array($log_level, $levelInfo) && in_array($level, $levelInfo)
                || in_array($level, $levelErrors)) {
           $writeLog = true; 
        }
        
        // if debug level then log everything
        if($log_level == Zend_log::DEBUG) {
           $writeLog = true; 
        }
        
        if($writeLog) {
            
            $trace = debug_backtrace();
            $mock_data = Mage::getStoreConfig('dev/smartling/mock_data');
            
            $lineMessage = $trace[0]['file'] . '. Line: ' .$trace[0]['line'];
            
            if($mock_data == 1) {
                $message = 'Mock mode is enabled. ' . $message;
            }
            
            Mage::log($message . ' in ' . $lineMessage, $level, $log_file);
        }
    }
    
    /**
     * 
     * @return array
     */
    public function getLogLevels() {
        $options = 
        array(
            Zend_log::EMERG => 'Emergency',
            Zend_log::ALERT   => 'Alert',
            Zend_log::CRIT    => 'Critical',
            Zend_log::ERR     => 'Error',
            Zend_log::WARN    => 'Warning',
            Zend_log::NOTICE  => 'Notice',
            Zend_log::INFO    => 'Informational',
            Zend_log::DEBUG   => 'Debug'
        );
        
        return $options;
    }    
    
    /**
     * 
     * @param int $completedStringCount
     * @param int $stringCount
     * @return int
     */
    public function calculatePercent($completedStringCount, $stringCount) {
        
        if($stringCount == 0) {
            return 0;
        }
        
        $percent = ($completedStringCount / $stringCount) * 100;
        
        return round($percent);
    }
    
    /**
     * Launch cron job
     * @param string $key
     */
    public function scheduleNow($key = 'upload_bulk_content') {
        
        $schedule = Mage::getModel('cron/schedule'); /* @var $schedule Aoe_Scheduler_Model_Schedule */
        
        if( ($schedule instanceof Aoe_Scheduler_Model_Schedule) == false ) {
            return false;
        }
        
        $schedule->setJobCode($key)
                            ->runNow(false) // without trying to lock the job
                            ->save();

        $messages = $schedule->getMessages();

        if ($schedule->getStatus() == Mage_Cron_Model_Schedule::STATUS_SUCCESS) {
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Ran "%s" (Duration: %s sec)', $key, intval($schedule->getDuration())));
                if ($messages) {
                        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('"%s" messages:<pre>%s</pre>', $key, $messages));
                }
        } else {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Error while running "%s"', $key));
                if ($messages) {
                        Mage::getSingleton('adminhtml/session')->addError($this->__('"%s" messages:<pre>%s</pre>', $key, $messages));
                }
        }
        
    }
    
    /**
     * Merge original and received data for options
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $model
     * @param type $data
     * @return void
     */
    public function mergeAttributesOptions(Mage_Catalog_Model_Resource_Eav_Attribute &$model, &$data, $destinationStoreId) {
        
        /** @var $helperCatalog Mage_Catalog_Helper_Data */
        $helperCatalog = Mage::helper('catalog');
        
        // set default value
        $model->setStoreId(0);
        
        /** @var array $options - List of original options */
        $options = array($model->getSource()->getAllOptions(false));
        
        // [[ re-format received data option array
        $optionValues = array();

        if (!empty($data['option']) && is_array($data['option'])) {
            foreach ($data['option'] as $key => $values) {
                $optionValues['option']['value'][$key][$destinationStoreId] = $helperCatalog->stripTags($values['value']);
            }
        }

        unset($data['options_values']);
        $data['option'] = $optionValues['option'];
        // ]] re-format received data option array
        
        $frontend_label = $data['frontend_label'];
        $data['frontend_label'] = array($model->getStoreLabel(0));
        
        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                
                foreach ($stores as $store) {
                    
                    $storeId = $store->getId();
                    
                    $model->setStoreId($storeId);
                    $options[$storeId] = $model->getSource()->getAllOptions(false);
                    
                    if($storeId != $destinationStoreId) {
                        $data['frontend_label'][$storeId] = $model->getStoreLabel($storeId);
                    } else {
                        $data['frontend_label'][$storeId] = $frontend_label;
                    }
                }
            }
        }
        ksort($data['frontend_label']);
        
        //[[ merge original and received options
        foreach ($options as $storeId => $storeOptions) {
            foreach ($storeOptions as $value) {
                $option_id = $value['value'];
                
                if(!isset($data['option']['value'][$option_id][$storeId])) {
                    $data['option']['value'][$option_id][$storeId] = $value['label'];
                }
                    
                ksort($data['option']['value'][$option_id]);
            }
        }
        //]] merge original and received options
        
        //[[ set option sort order
        $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
            ->setAttributeFilter($model->getId())
            ->setPositionOrder('desc', true)
            ->load();

        foreach ($optionCollection as $option) {
            $data['option']['order'][$option->getId()] = $option->getSortOrder();
        }
        //]] set option sort order
        
        return $data;
        
    }
    
    /**
     * 
     * @return array
     */
    public function getLocalizationDirectoriesList($showSelectText = false) {
        
        $options = array();
        
        if($showSelectText) {
            $options[] = Mage::helper('connector')->__('Please select directory of localization dir');
        }
        
        $locale = Mage::getBaseDir('locale');
        
        $dirs = new Varien_Directory_Collection($locale, false);
        foreach ($dirs as $item) {
            $code = $item->getDirName();
            $options[$code] = $code;
        }
        
        return $options;
    }
    
    /**
     * 
     * @param type $requestType
     * @param type $id
     * @param type $entityGlobalType
     * @return array
     */
    public function changeStatus($requestType, $id, $entityGlobalType, $keyField) {
        
        $result = array('status' => 1);
        
        try {
            
            $type = Mage::getModel('connector/content_types')
                ->getTypeDetails($requestType);
            $content_types_id = $type->getTypeId();
            
            $itemModel = Mage::getModel('connector/translate_' . $entityGlobalType);
        
            $itemsCollection = $itemModel->getCollection();
            $itemsCollection->addFieldToFilter($keyField, $id)
                            ->addFieldToFilter('entity_type_id', $content_types_id);

            if($itemsCollection->getSize()) {
                $itemsCollection->getFirstItem()->delete();
                $result['message'] = Mage::helper('connector')
                        ->__('Item was dettached successfully');
            } else {
                $dataToSave = array($keyField => $id, 
                                    'entity_type_id' => $content_types_id);
                $itemModel->setData($dataToSave)->save();
                
                $result['message'] = Mage::helper('connector')
                        ->__('Data has saved successfully');
            }
        } catch (Mage_Core_Exception $e) {
          $result['message'] = Mage::helper('connector')
                  ->__('Application internal error. Please see smartling log for details');
          Mage::helper('connector')->log($e->getMessage(), Zend_log::ERR);
          $result['error'] = $e->getMessage();
          $result['status'] = 0;
        }
                        
        return $result;
    }
    
}
