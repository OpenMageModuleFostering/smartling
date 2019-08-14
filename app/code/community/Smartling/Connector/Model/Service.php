<?php

/**
 * Description of CallUpload
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Service 
    extends Mage_Core_Model_Abstract
{
    /**
     *
     * @var string 
     */
    protected $_uri = '';
    
    /**
     *
     * @var int 
     */
    protected $_step = 5;
    
    /**
     * 
     */
    protected $_maxThreads;
    
    /**
     * Lock file name
     */
    protected $_lockFileName = 'smartling_check_status_process.lock';
    
    /**
     * Check status lock status
     * @var null|boolean
     */
    protected $_isLocked = null;
    
    /**
     * 
     * @param string | null $uri
     */
    public function __construct($uri = '') {
        if ($uri != '') {
            $this->_uri = $uri;
        }
        
        $this->_maxThreads = Mage::getStoreConfig('connector/max_threads');
    }
    
    /**
     * 
     * @param string $uri
     * @return \Smartling_Connector_Model_CallUpload
     */
    public function setUri($uri) {
        $this->_uri = $uri;
        return $this;
    }
    
    /**
     * run upload process
     * 
     * @return bool
     */
    public function callUpload($content_id = 0) {   
        
        /** @var $contentType Smartling_Connector_Model_Content */
        $contentType = Mage::getModel('connector/content');
        $collection = $contentType->getNewItems();  
        
        $errors = array();
        
        if(is_numeric($content_id) && $content_id) {
            $collection->addFieldToFilter('content_id', $content_id);
        }
        
        if (!$collection->getSize()) {
            return false;
        }
        
        foreach($collection as $item) {
            
            /** @var $contentModel Smartling_Connector_Model_Content_Abstract */
            $contentModel = Mage::getModel($item['model']);
            
            try { 

                if(($contentModel instanceof Smartling_Connector_Model_Content_Abstract) == false) {
                    Mage::throwException("Application error. Model '{$item['model']}' does not of required type");
                }

                $instanceModel = $contentModel->getContentTypeEntityModel()
                                              ->load($item['origin_content_id']);

                if ($instanceModel->getId()) {
                    $content = $contentModel->createTranslateContent($instanceModel, $item['project_id'], $item['source_store_id']);

                    if(!$content || $content == '-1') {

                        $errMessage = "Upload content: Content is empty." 
                                        . " Content type: {$contentModel->getContentTypeCode()}." 
                                        . " Content id: {$instanceModel->getId()}";

                        Mage::throwException($errMessage);
                    }

                    $fileUri = ($item['filename']) ? $item['filename'] : $contentModel->getFileUri();

                    $responseSuccessStatus = $contentModel->uploadContent($content, $fileUri, $item);

                } else {
                    $fileUri = $item['filename'];
                }

                if(!$item['filename']) {
                    $item['filename'] = $fileUri;
                }

                /**
                 * if successfully uploaded - update data in db about content item
                 */
                if ($responseSuccessStatus) { 
                    /**
                     * write event to logger                
                     */
                    $message = Mage::helper('connector')->__('File "%s" for locale %s has been uploded', 
                            $item['filename'],
                            $item['locales']);

                    Mage::helper('connector')->log($message, Zend_log::INFO);

                    $status = Smartling_Connector_Model_Content::CONTENT_STATUS_PROCESS;
                    $updateData = array('content_id' => $item['content_id'],
                                        'filename'   => $item['filename'],
                                        'status'     => $status,
                                        'content_title' => $contentModel->getContentTitle(),
                                        'locales' => $item['locales'],
                                        'origin_content_id' => $item['origin_content_id'],
                                        'project_id' => $item['project_id']
                                       );
                    $contentType->setData($updateData);                
                    try {
                        $contentType->save();
                    } catch (Mage_Exception $e){
                        Mage::helper('connector')->log($e->getMessage(), Zend_log::ERR);
                        Mage::throwException($e->getMessage());
                    }

                } else {

                    $message = Mage::helper('connector')->__('Unable to upload "%s" for locale %s', 
                            $contentModel->getFileUri(),
                            $item['locale']                        
                            );

                    Mage::helper('connector')->log($message, Zend_log::ERR);
                    Mage::throwException($message);
                }

            } catch (Mage_Core_Exception $e) {
              $errors[] = $e->getMessage();
            } catch (Exception $e) {
              $errors[] = Mage::helper('connector')->__('Sorry. Internal application error.');
            }
            
            gc_collect_cycles();
            flush();
        }
        
        if(sizeof($errors)) {
            Mage::helper('connector')->log(implode("\n", $errors), Zend_log::ERR);
            return false;
        }
        
        return true;
    }    
    
    /**
     * Run update statues process. Download file for ready entities.
     *
     * @param mixed $force
     * @return boolean
     */
    public function updateStatus($force = false) {
        
        $attributesOptions = Mage::getSingleton('connector/content_attributes_options');
        $contentCollection = $this->buildColection();
        
        if(!$contentCollection) return false;
        
        Mage::dispatchEvent('smartling_check_statuses_before', 
                            array(
                                'collection' => $contentCollection
                            ));
        
        if($this->isLocked()) {
            Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('connector')->__('Sorry, check status process is locked')
                        );
            return false;
        } else {
            $this->lock();
        }
        
        foreach ($contentCollection as $item) {
            
            $cmsContentModel = Mage::getSingleton($item->getModelClass());
            
            Mage::register('content_translate_row_data', $item->getData());
                    
            try {
                
                /** @var Smartling_Connector_Model_Translator */
                $connector = Mage::getModel('connector/translator', 
                                                    array(
                                                      'apiKey' => $item['api_key'], 
                                                      'projectId' => $item['project_code'],
                                                      'project_id' => $item['project_id']
                                                    )
                        );
                        
                // check status of translation
                $statusResponse = $connector->checkStatus($item['filename'], $item['locale']);
                
                // check of unexpected format
                if (!Mage::helper('connector')->isJson($statusResponse)) {
                    Mage::throwException(
                            Mage::helper('connector')
                                ->__('Invalid response. Expected type is JSON. Received: ') 
                            . $statusResponse
                                    );
                } else {
                    $statusResponseData = json_decode($statusResponse, true);
                    $responseData = array();
                    
                    if(isset($statusResponseData['response'])) { 
                        $responseData = $statusResponseData['response'];
                    }
                    
                    if(isset($responseData['messages']) && $responseData['code'] != 'SUCCESS') {
                        Mage::throwException(implode('; ', $responseData['messages']));
                    }
                    
                    $percent = 
                        Mage::helper('connector')->calculatePercent(
                                                    @$responseData['data']['completedStringCount'], 
                                                    @$responseData['data']['stringCount']
                                                );
                    
                    $item->setPercent($percent);
                }
                
                if($force === true || $percent == 100) {
                    
                    $response = $connector->downloadTranslatedContent($item['filename'], 
                                                                      $item['locale'], 
                                                                      array('retrievalType' => $item['retrieval_type'])
                                            );
                
                    $translatedContent = $cmsContentModel->getTranslatedContent($response);

                    $newContent = $cmsContentModel
                                    ->createContent(
                                                    $translatedContent, 
                                                    $item['origin_content_id'], 
                                                    $item['store_id']
                                                   );

                    Mage::dispatchEvent('smartling_apply_content_after', 
                            array(
                                'new_content' => $newContent,
                                'cms_content_model_instanse' => $cmsContentModel,
                                'item' => $item,
                                'response' => $response,
                            ));
                }
                
            } catch (Mage_Core_Exception $e) {
                Mage::helper('connector')->log($e->getMessage(), Zend_log::ERR);
                Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('connector')->__($e->getMessage())
                        );
            } catch (Exception $e) {
                Mage::helper('connector')->log($e->getMessage(), Zend_log::ERR);
                Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('connector')->__($e->getMessage())
                        );
            }
            
            Mage::unregister('content_translate_row_data');    
        }
        
        Mage::dispatchEvent('smartling_check_statuses_after', 
                            array(
                                'attributes_options_instanse' => $attributesOptions,
                                'items_collection' => $contentCollection
                            ));
        
        return true;
    }        
    
    /**
     * Build and filter collection of none translated entities
     * @return false|\Smartling_Connector_Model_Resource_Content_Collection 
     */
    protected function buildColection() 
    {
        $content_id  = $this->getContentId();
        $origin_content_id = $this->getOriginContentId();
        $project_id  = $this->getProjectId();
        $storeId = $this->getStoreId();
        
        try {
            $contentCollection = Mage::getModel('connector/content')->getCollection();
            $contentCollection->addFieldToFilter('filename', array('neq' => ''))
                              ->addFieldToFilter('percent', array('lt' => '100'));

            if($content_id) { // identity field
                $contentCollection->addIndentifyToFilter($content_id);
            } elseif($origin_content_id && $project_id && $storeId) {
                $contentCollection->addUniqueByParams($origin_content_id, $project_id, $storeId);
            } else { // get all none updated records
                $contentCollection->setPageSize(500)
                                  ->setCurPage(1);
            }
        
            $contentCollection->joinAdditionalDetails();
            
            return $contentCollection;
        } catch (Exception $ex) {
            Mage::helper('connector')->log($ex->getMessage(), Zend_log::ERR);
            return false;
        }
        
        
    }
    
    /**
     * Get lock file resource
     *
     * @return resource
     */
    protected function _getLockFile()
    {
        if ($this->_lockFile === null) {
            $file = $this->getLockFilePath();
            if (is_file($file)) {
                $this->_lockFile = fopen($file, 'w');
            } else {
                $this->_lockFile = fopen($file, 'x');
            }
            fwrite($this->_lockFile, date('r'));
        }
        return $this->_lockFile;
    }
    
    protected function getLockFilePath() {
        $varDir = Mage::getConfig()->getVarDir('locks');
        $file = $varDir . DS . $this->_lockFileName;
        return $file;
    }

    /**
     * Lock process without blocking.
     * This method allow protect multiple process runing and fast lock validation.
     *
     * @return Smartling_Connector_Model_Service
     */
    public function lock()
    {
        $this->_isLocked = true;
        flock($this->_getLockFile(), LOCK_EX | LOCK_NB);
        return $this;
    }

    /**
     * Lock and block process.
     * If new instance of the process will try validate locking state
     * script will wait until process will be unlocked
     *
     * @return Smartling_Connector_Model_Service
     */
    public function lockAndBlock()
    {
        $this->_isLocked = true;
        flock($this->_getLockFile(), LOCK_EX);
        return $this;
    }

    /**
     * Unlock process
     *
     * @return Smartling_Connector_Model_Service
     */
    public function unlock()
    {
        try {
            if($this->isLocked()) {
                $this->_isLocked = false;
                @flock($this->_getLockFile(), LOCK_UN);
                @unlink($this->getLockFilePath());
            }
        } catch (Mage_Exception $e) {
            Mage::helper('connector')->log($e->getMessage(), Zend_log::ERR);
            Mage::throwException($e->getMessage());
        }
        return $this;
    }

    /**
     * Check if process is locked
     *
     * @return bool
     */
    public function isLocked()
    {
        if ($this->_isLocked !== null) {
            return $this->_isLocked;
        } else {
            $fp = $this->_getLockFile();
            if (flock($fp, LOCK_EX | LOCK_NB)) {
                flock($fp, LOCK_UN);
                return false;
            }
            return true;
        }
    }

    /**
     * Close file resource if it was opened
     */
    public function __destruct()
    {
        if ($this->isLocked()) {
            $this->unlock();
        }
    }
    
}
