<?php

/**
 * Description of Translator
 *
 * @author snail
 */


class Smartling_Connector_Model_Translator 
    extends Mage_Core_Model_Abstract
{
    
    /**
     * xml file type for Smartling Api while uploading files 
     */
    const FILE_TYPE_XML = "xml";
    
    /**
     * CSV file type for Smartling Api while uploading files 
     */
    const FILE_TYPE_CSV = "csv";
    
    /**
     * test mode Smartling API
     */
    const TEST_MODE = 'SANDBOX';
    
    /**
     * production mode Smartling API
     */
    const PRODUCTION_MODE = 'PRODUCTION';
    
    /**
     *
     * @var null | SmartlingAPI
     */
    protected $_smartlingApiModel = null;
    
    /**
     *
     * @var string
     */
    protected $_code = 'smartling';
    
    /**
     *
     * @var string 
     */
    protected $_apiKey = null;
    
    /**
     *
     * @var string 
     */
    protected $_projectId = null;
    
    
    /**
     * List of Smartling API lib objects
     * @var array 
     */
    protected $_smartlingObjectPool = null;
    
    /**
     * List of Smartling API data
     * @var array 
     */
    protected $_projectsDataPool = null;
    
    /**
     * @var string 
     */
    protected $_objectPoolKeyPrefix = 'smartling_';
    
    
    protected function _construct() {
        $this->_init('connector/translator');
    }
    
    /**
     * instantiate SmartlingApi object
     * 
     * @params $options
     */
    public function __construct($options = array()) {
        
        if (sizeof($options)) {
            $this->setProjectData($options);
        }
        
        if($options['project_id']) {
            $this->initSmartlingApiInstance($options['project_id']);
        }
        
        $this->_fileType = self::FILE_TYPE_XML;
    }
    
    protected function getProjectSettings($project_id) {
        return $this->_projectsDataPool[$project_id];
    }

    /**
     * 
     * @param array $options
     */    
    protected function setProjectData(array $options) {
        if(is_null($this->_projectsDataPool[$options['project_id']]) && $options['project_id']) {
            
            if(isset($options['locales']) && !is_array($options['locales'])) {
                $options['locales'] = explode(',', $options['locales']);
            }
            
            $this->_projectsDataPool[$options['project_id']] = $options;
        }
    }

        /**
     * Deprecated in newest version
     * 
     * @param string $path
     * @param string $fileUri
     * @return string
     */
    public function uploadTranslateData($path, $fileUri, $params = array()){
        
        $fileType = $this->_getFileType();
        $project_id = $this->getCurrentProjectId();
        $locales = $this->_projectsDataPool[$project_id]['locales'];
        
        $upload_params = new FileUploadParameterBuilder();
        $upload_params->setLocalesToApprove($locales)
                      ->setFileUri($fileUri)
                      ->setFileType($fileType)
                      ->setOverwriteApprovedLocales(0)
                      ->setApproved(0);
         
         Mage::helper('connector')->log("Upload file " . $path, Zend_log::DEBUG);
         
         $params = array_replace_recursive($upload_params->buildParameters(), $params);
                 
         $responce = $this->_smartlingApiModel
                        ->uploadFile($path, $params);

         return $responce;
    }   
    
    /**
     * upload content for translation
     * 
     * @param type $content
     * @param type $fileUri
     * @return string
     */
    public function uploadTranslateContent($content, $fileUri){
        
        $fileType = $this->_getFileType();
        $project_id = $this->getCurrentProjectId();
        $locales = $this->_projectsDataPool[$project_id]['locales'];
        
        $upload_params = new FileUploadParameterBuilder();
        $upload_params->setLocalesToApprove($locales)
                      ->setFileUri($fileUri)
                      ->setFileType($fileType)
                      ->setOverwriteApprovedLocales(0)
                      ->setApproved(0);
         
        Mage::helper('connector')->log($content, Zend_log::DEBUG);
		 
		$tmpfname = tempnam("/tmp", "smartling_");
        $handle = fopen($tmpfname, "w");
        fwrite($handle, $content);
        fclose($handle);

        $responce = $this->_smartlingApiModel
                        ->uploadFile($tmpfname, $upload_params->buildParameters());
        unlink($tmpfname);


         return $responce;
    }
    
    /**
     * 
     * @param string $locale
     * @return string
     */
    public function getTranslationsInfo($locale, $params = null){
        return $this->_smartlingApiModel
                ->getList($locale, $params);
    }
    
    /**
     * 
     * @param string $fileUri
     * @param string $locale
     * @param array $params
     * @return string
     */
    public function downloadTranslatedContent($fileUri, $locale, $params = array()){
        return $this->_smartlingApiModel
                ->downloadFile($fileUri, $locale, $params);
    }
    
    /**
     * 
     * @param string $fileUri
     * @param string $locale
     * @param array $params
     * @return string
     */
    public function checkStatus($fileUri, $locale){
        return $this->_smartlingApiModel
                ->getStatus($fileUri, $locale);
    }
    
    /**
     * 
     * @param int $mage_project_id
     * @return boolean|\SmartlingAPI
     * @throws Varien_Exception
     */
    protected function initSmartlingApiInstance($mage_project_id) {
        
        $this->setCurrentProjectId($mage_project_id);
        
        if(!isset($this->_projectsDataPool[$mage_project_id])) {
            $collection = Mage::getModel('connector/projects')->getProjectData($mage_project_id);
            if($collection !== false) {
                $this->_projectsDataPool[$mage_project_id]['apiKey'] = $collection->getKey();
                $this->_projectsDataPool[$mage_project_id]['projectId']  = $collection->getProjectsId();
            } else {
               return false;
            }
        }
        
        
        $smartlingObjectPoolKey = $this->getPoolObjectKey($mage_project_id);
        
        $smartlingObject = Mage::objects($smartlingObjectPoolKey);
        
        if($smartlingObject instanceof Smartling_Connector_Model_SmartlingAPI) {
            $this->_smartlingApiModel = $smartlingObject;
            return $smartlingObject;
        } elseif( ($smartlingObject instanceof Smartling_Connector_Model_SmartlingAPI)===false && isset($smartlingObject)) {
            throw new Varien_Exception('Smartling Object with key ' . $smartlingObjectPoolKey . ' has unexpected type.');
        }
        
        $smartlingObject = $this->getApi(null,
                                         $this->_projectsDataPool[$mage_project_id]['apiKey'],
                                         $this->_projectsDataPool[$mage_project_id]['projectId'],
                                         $this->_mode()
                                        );
        
        Mage::objects()->save($smartlingObject, $smartlingObjectPoolKey);
        
        $this->_smartlingApiModel = & $smartlingObject;
        
        return $this->_smartlingApiModel;
    }
    
    /**
     * Init object with API params for test connection
     * @param array $options
     * @return SmartlingAPI
     */
    protected function setSmartlingApiInstance($options) {
        
        Mage::helper('connector')->log($options, Zend_log::DEBUG);
        Mage::helper('connector')->log($this->_mode(), Zend_log::DEBUG);
        
        $smartlingObject = $this->getApi(null,
                                         $options['apiKey'],
                                         $options['projectId'],
                                         $this->_mode()
                                        );
        
        $this->_smartlingApiModel = & $smartlingObject;
        
        return $this->_smartlingApiModel;
    }
    
    /**
     * 
     * @param int $mage_project_id
     * @return string
     */
    private function getPoolObjectKey($mage_project_id) {
        return $this->_objectPoolKeyPrefix . $mage_project_id;
    }

     /**
     * @param int $mage_project_id
     * @return \SmartlingApi_lib_SmartlingAPI
     */
    public function getSmartlingApiInstance($mage_project_id) {
        
        $smartlingObjectPoolKey = $this->getPoolObjectKey($mage_project_id);
        $smartlingObject = Mage::objects($smartlingObjectPoolKey);
        
        if (is_null($smartlingObject)) {
            return $smartlingObject;
        } else {
            return $this->initSmartlingApiInstance($mage_project_id);
        }
    }
    
    /**
     * Depricated. Use credentials from projects list instead.
     * 
     * @return string
     */
    protected function _getApiKey(){
        if (is_null($this->_apiKey)){
            
            if ($apiKey = $this->_getConfigData('settings/api_key')){
                $this->_apiKey = Mage::helper('core')->decrypt($apiKey);
            } else {
                $this->_apiKey = '';
            }
        }        
        return $this->_apiKey;
    }
    
    /**
     * Depricated. Use credentials from projects list instead.
     * 
     * @return string
     */
    protected function _getProjectId(){
        if (is_null($this->_projectId)){
            
            if ($projectId = $this->_getConfigData('settings/project_id')){
                $this->_projectId = Mage::helper('core')->decrypt($projectId); 
            } else {
                $this->_projectId = "";
            }
        }        
        return $this->_projectId;
    }
    
    /**
     * 
     * @param string $path
     * @return string
     */
    protected function _getConfigData($path){
        return Mage::helper('connector')->getConfig($this->_code, $path);
    }
    
    /**
     * @return bool
     */
    protected function _getMode(){
        if (!is_null(Mage::getStoreConfig('dev/smartling/mode'))){
           if (Mage::getStoreConfig('dev/smartling/mode') == false){
               return false;
           }
        }
       return true;
    }
    
    /**
     * @return string
     */
    protected function _mode(){
        if ($this->_getMode()){
            return self::TEST_MODE;
        } else {
            return self::PRODUCTION_MODE;
        }
    } 
    
    /**
     * 
     * @return string | null
     */
    protected function _getFileType(){        
        return $this->_fileType;
    }
    
    /**
     * 
     * @return string | null
     */
    public function setFileType($type){ 
        $this->_fileType = $type;
    }
    
    /**
     * @return stdClass | bool
     */
    public function convertResponse($response){
        if (Mage::helper('connector')->isJson($response)){           
            return json_decode($response);
        }
        return false;
    }
    
    /**
     * creates new/update existing rows in submissions table in case when content
     * upload successfully.
     * 
     * @param array $locale
     * @param array $data
     */
    public function createFilesInProcess($locale, $data){        
        $process = Mage::getModel('connector/content');              
        $stores = implode(",", Mage::helper('connector')->getStoresIdByLocale($locale));
        $process->setData($data)
                ->setLocale($locale)
                ->setStoreId($stores)
                ->setPercent(0.00)
                ->setStatus(Smartling_Connector_Model_Content::CONTENT_STATUS_PROCESS);
        if ($translatedId = $this->findIdentityContent($stores, $data['origin_content_id'])) {
            $process->setTranslatedContentId($translatedId);
        }
        try{
            $process->save();
        } catch(Mage_Exception $e){
            Mage::helper('connector')->log($e->getMessage(), Zend_log::ERR);
        }             
    }
    
    /**
     * 
     * @param int $typeId
     * @return string
     */
    public function getModelByContentType($typeId){
        $contentType = Mage::getModel('connector/content_types')->load((int)$typeId);
        if (!$contentType->getId()){
            Mage::getSingleton('admin/session')->addError(
                    Mage::helper('connector')->__('This type is not exists')
                    );
            return;
        }        
        return $contentType->getModel();
    }
    
    /**
     * 
     * @param string $fileUri
     * @param string $locale
     * @return string (json)
     */
    public function updateContentStatus($fileUri, $locale){
        return $this->_smartlingApiModel->getStatus($fileUri, $locale);
    }
    
    /**
     * defines percent
     * 
     * @param int $approved
     * @param int $completed
     */
    public function translatedPart($approved, $completed){
        if ($approved !== 0){
            $result = ($completed/$approved)*100;
        } else {
            $result = 0.00;
        }        
        return number_format($result, 2, '.', '');
    }
    
    /**
     * find existing row in submission table with the stores and origin content id
     * for updating it in future for cases when user download the same content 
     * 
     * @param type $stores
     * @param type $originContent
     */
    public function findIdentityContent($stores, $originContent){
        $contentCollection = Mage::getModel('connector/content')->getCollection()
                        ->addFieldToSelect('translated_content_id')    
                        ->addFieldToFilter('store_id', array('eq' => $stores))
                        ->addFieldToFilter('origin_content_id', array('eq' => $originContent));
       if ($contentCollection < 1){
           return false;
       }
       $contentId = $contentCollection->getFirstItem()->getData('translated_content_id');       
       return $contentId;                    
    }
    
    /**
     * upload new content to Smartling for translation. Check result. In case of 
     * successfull result save info about uploaded content to submission table. 
     * In case of errors save errors messages to session and show in further 
     * 
     * @param Smartling_Connector_Model_Content $contentModel
     * @param Vairen_Object $contentObject
     * @param array $processData
     * @param string $file
     */
    public function uploadContent($contentModel, $locales, $processData, $content){        
        $entry = $this->_findContent($processData, $locales);
        if (!empty($entry)) {
            $fileUri = $entry['filename'];
            $processData['content_id'] = $entry['content_id'];
        } else {
            $fileUri = $contentModel->getFileUri();
        }
        $response = $this->uploadTranslateContent($content, $fileUri);

        Mage::helper('connector')->log($response, Zend_log::INFO);
        
        if ($response && $this->convertResponse($response)){
            $result = $this->convertResponse($response);            
            if ($result->response->code == 'SUCCESS') {                
                Mage::getSingleton('adminhtml/session')
                       ->addSuccess(
                               Mage::helper('connector')->__("Content has been successfully sent to Smartling")
                               );

                //add uploaded content data to database
                $processData['filename'] = $fileUri;
                $this->createFilesInProcess($locales, $processData); 

                
                $message = Mage::helper('connector')->__('New file "%s" from "%s" for locales %s has been created', 
                        $fileUri,
                        $processData['content_title'],
                        implode(",", $locales)
                        );
                Mage::helper('connector')->log($message, Zend_log::INFO);

            } else {
                if (is_array($result->response->messages)){
                    $error = implode("; ", $result->response->messages);
                } else {
                    $error = $result->response->messages;
                }
                Mage::getSingleton('adminhtml/session')
                 ->addError(Mage::helper('connector')->__($error));
            }
        } else {
            Mage::getSingleton('adminhtml/session')
                 ->addError(Mage::helper('connector')->__("Incorrect response data"));
        } 
    }
    
    /**
     * 
     * @param string $key
     * @return \Smartling_Connector_Model_Translator
     */
    public function setApiKey($key){
        $this->_apiKey = $key;
        return $this;
    }
    
    /**
     * 
     * @param string $projectId
     * @return \Smartling_Connector_Model_Translator
     */
    public function setProjectId($projectId){
        $this->_projectId = $projectId;
        return $this;
    }
    
    /**
     * 
     * @param string $response
     * @return boolean
     */
    public function isSuccessResponse($response) {
        if ($response == '' || is_null($response)){
            return false;
        }
        
        $result = $this->convertResponse($response);
        
        if ($result->response->code == 'SUCCESS') {
            return true;
        }
        
        return false;
    }
    
    /**
     * 
     * @param array $name
     * @param string $locales
     */
    protected function _findContent($data, $locales) {
        $resource = Mage::getResourceModel('connector/content');
        return $resource->findContent($data, $locales);    
    }
    
    public function getApi() {
        
        $args = func_get_args();
        
        return Mage::getModel('connector/SmartlingAPI', $args);
    }
}
