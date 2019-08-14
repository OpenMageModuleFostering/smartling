<?php

/**
 * Description of Attibute
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Content_Localization 
    extends Smartling_Connector_Model_Content_Abstract
        implements Smartling_Connector_Model_Content_Interface
{
    
    const CONTENT_TYPE = 'localization';
    
    /**
     * define entity type 
     * 
     * @var string 
     */
    protected $_entityType = 'translate';
    
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
     *
     * @var string 
     */
    protected $_uploadFilePath;
    
    
    protected $_comments = array(
            'smartling.field_separator'=> ',',
            'smartling.string_encloser' => '"',
            'smartling.string_format_paths' => 'html : 2',
            'smartling.paths' => '2',
            'smartling.source_key_paths' => '1',
            'smartling.placeholder_format_custom' => '(%\w{1})|(\{\{.+\}\})'
        );
    
    public function __construct() {
        parent::__construct();
        $this->_fileTypeModel = 'connector/types_general';
    }
    
    
    /**
     * Creates xml content for translation
     * Also defines filename uri for upload content to Smartling via API
     * Defines content title for smartling translation table
     * 
     * @param Smartling_Connector_Model_Content_Localization|int $localization
     * @param int $project_id Profile ID
     * @param int $sourceStoreId Source locale
     * @return string
     */
    public function createTranslateContent($localization, $project_id, $sourceStoreId) {
        
        if (is_numeric($localization)) {
            $localization = $this->getContentTypeEntityModel()
                            ->setStoreId($sourceStoreId)
                            ->load($localization);
        }
        
        if (!$localization->getId()){
            Mage::getModel('adminhtml/session')->addError(
                    Mage::helper('connector')->__('File does not exists')
                    );
            return false;
        }

        $this->setUploadFilePath($localization->getFilePath());
        
        $this->_fileUri = $this->formatFileUri('localization_file', $localization->getId(), $project_id);
        $this->_title = $localization->getTitle();
        
        return $localization->getFilePath();
    }    
        
    
    /**
     * 
     * @param array $translatedContent
     * @param int $fileId
     * @param int $storeId
     * @param Smartling_Connector_Model_Content $contentModel
     * @return bool
     */
    public function createContent($translatedContent, $fileId, $storeId){
        
        $translatedContentData = Mage::registry('content_translate_row_data');
        
        $destinationLocation = 
                $this->getDestinationLocation($translatedContentData['store_id'], 
                                              $translatedContentData['source_store_id'], 
                                              $translatedContentData['file_path']);
                
        if(!$destinationLocation) {
            Mage::helper('connector')->log("Destination directory has not specified", Zend_log::ERR);
            return false;
        }
        
        if($this->filePutContent($destinationLocation, $translatedContent) === false) {
            Mage::helper('connector')->log("Can not create {$destinationLocation} file", Zend_log::ERR);
            return false;
        }
        
        return true;
    }
    
    /**
     * 
     * @param string $filePath
     * @param string $content
     * @return boolean
     */
    public function filePutContent($filePath, $content) {
        
        try {
            if(!is_dir(dirname($filePath))) {
              $this->createDirRecursive(dirname($filePath));
            }
            
            $handle = fopen($filePath, 'w');
            
            if ($handle === false) {
                Mage::throwException("Cannot open file ($filePath)");
            }
            
            if (fwrite($handle, $content) === false) {
                Mage::throwException("Cannot write to file ($filePath)");
            }
            
            fclose($handle);
            
        } catch (Mage_Core_Exception $e) {
          Mage::helper('connector')->log($e->getMessage(), Zend_log::ERR);
          return false;
        }
        
        return true;
    }
    
    private function createDirRecursive($path, $permissions = 0777) {
        if (is_dir($path)) return true;
        $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1 );
        $return = $this->createDirRecursive($prev_path, $permissions);
        return ($return && is_writable($prev_path)) ? mkdir($path) : false;
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
        return Mage::getModel('connector/localization_files_index');
    }
    
     /**
     * 
     * @return boolean
     */
    public function saveNames() {
        
        try {
            
            $resource = Mage::getSingleton('core/resource');
            $_adapter = $resource->getConnection('core_write');

            $model = $this->getContentTypeEntityModel();

            $entityTableName = $resource->getTableName($model->getResourceName());
            $contentTableName = $resource->getTableName('connector/translate_content');
            
            $updateNamesQuery = "UPDATE {$contentTableName} c "
                                    . " JOIN {$entityTableName} AS `e` ON c.content_title = '' " 
                                        . " AND c.origin_content_id = e.id "
                                    . " SET c.content_title = substring_index(e.file_path, '/', -1)";
            
            $_adapter->query($updateNamesQuery);
            
        } catch (Exception $ex) {
            Mage::helper('connector')->log("Current entity names (Flat Model) weren't updated: " . $ex->getMessage(), Zend_log::ERR);
            return false;
        }
        
        return true;
        
    }
    
    /**
     * 
     * @param string $path
     * @param string $fileUri
     * @param array $item
     * @return bool
     */
    public function uploadContent($path, $fileUri, $item) {
        
        /** @var $translator Smartling_Connector_Model_Translator */
        $translator = Mage::getModel('connector/translator', 
                                            array('apiKey' => $item['api_key'], 
                                                  'projectId' => $item['project_code'],
                                                  'project_id' => $item['project_id'],
                                                  'locales' => $item['locales']
                                              )
                                         );
        $translator->setFileType(Smartling_Connector_Model_Translator::FILE_TYPE_CSV);

        $response = $translator->uploadTranslateData($path, $fileUri, $this->_comments);
        
        Mage::helper('connector')->log($response, Zend_log::INFO);
        
        return $translator->isSuccessResponse($response);
    }
    
    /**
     * 
     * @return string|bool
     */
    public function getFileUri() {
        if(strlen($this->_fileUri)) {
            return $this->_fileUri . '.csv';
        } else {
            return false;
        }
    }
    
    public function getComments() {
        return implode("\n", $this->_comments) . "\n";
    }
    
    public function setUploadFilePath($path) {
        $this->_uploadFilePath = $path;
    }
    
    /**
     * 
     * @return string
     */
    public function getUploadFilePath() {
        return $this->_uploadFilePath;
    }
    
    /**
     * Return parsed response 
     * 
     * @param string $translatedContent
     * @return string
     */
    public function getTranslatedContent($translatedContent) {
        return $translatedContent; // return itself as CSV file dont' need modifications
    }
    
    /**
     * 
     * @param int $destination_store_id
     * @param int $source_store_id
     * @param string $file_source_path
     * @return boolean
     */
    protected function getDestinationLocation($destination_store_id, $source_store_id, $file_source_path) {
        
        $locales = $this->gelLocalizationDirectories($destination_store_id, $source_store_id);
        
        if(sizeof($locales) != 2) {
            Mage::helper('connector')->log('Source and destination locales has not detected', Zend_log::ERR);
            return false;
        }
        
        $destinationFile = str_replace($locales[$source_store_id], 
                                       $locales[$destination_store_id], 
                                       $file_source_path);
        
        return $destinationFile;
    }
    
    /**
     * 
     * @param int $destination_store_id
     * @param int $source_store_id
     * @return boolean|array
     */
    public function gelLocalizationDirectories($destination_store_id, $source_store_id) {
        
        $locales = array();
        $storesCollection = Mage::getModel('core/store')->getCollection();
        $storesCollection->addFieldToFilter('store_id', 
                                                array('in' => 
                                                    array(
                                                          $destination_store_id, 
                                                          $source_store_id
                                                         )
                                                    ));
        
        try {
            
            if($storesCollection->getSize() < 2) {
                Mage::throwException("Store localization directory hasn't specified");
            }
            
            foreach ($storesCollection as $storeParams) {
                $storeLocalizationDir = $storeParams->getStoreLocalizationDir();
                
                if(is_null($storeLocalizationDir)) {
                    Mage::throwException("Store localization directory hasn't specified. Store name: {$storeParams->getName()}");
                }
                $locales[$storeParams->getId()] = $storeParams->getStoreLocalizationDir();
            }
            
        } catch (Mage_Core_Exception $e) {
          Mage::helper('connector')->log($e->getMessage(), Zend_log::ERR);
          return false;
        }
        
        return $locales;
        
    }
}
