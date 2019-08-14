<?php

/**
 * Description of ServiceController
 *
 * @author Smartling
 */
class Smartling_Connector_ServiceController 
    extends Mage_Core_Controller_Front_Action
{   
        
    /**
     * @deprecated since version 0.2.8
     * @return void
     */
    public function uploadAction() {        
        
        $contentData = $this->getRequest()->getPost('content_data');
        
        $errors = array();
        $messages = array();
        $result = array('status' => 0);
        
        if (!$contentData) {
            $errors[] = "Request has no parameters";
        }

        try {
            
            if (sizeof($errors)) {
                Mage::throwException('Submission has errors');
            }
            
            $item = unserialize($contentData);
            
            if(!is_array($item) || !sizeof($item)) {
                Mage::throwException('Data is empty');
            }
            
            /** @var $contentType Smartling_Connector_Model_Content */
            $contentType = Mage::getModel('connector/content');
            
            
                
            /** @var $contentModel Smartling_Connector_Model_Content_Abstract */
            $contentModel = Mage::getModel($item['model']);

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

                $messages[] = $message;

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
        
        if(sizeof($errors)) {
            Mage::helper('connector')->log(implode("\n", $errors), Zend_log::ERR);
            $result['messages']['errors'] = $errors;
        }
        
        if(sizeof($messages)) {
            $result['messages']['success'] = $messages;
        }
        
        if($this->getRequest()->isAjax()) {
            return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        } else {
            $this->_redirectReferer();
        }
    }
}