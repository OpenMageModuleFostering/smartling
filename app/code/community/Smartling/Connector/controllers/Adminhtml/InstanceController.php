<?php

/**
 * Description of InstanceController
 *
 * @author Smartling
 */
class Smartling_Connector_Adminhtml_InstanceController 
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * 
     */
    public function indexAction(){
        $this->_redirect('edit');
    }
    
    /**
     * edit or create new translations
     */
    public function editAction(){
        $this->_title(Mage::helper('connector')->__('Create New Translation Content'));
        $this->loadLayout();
        $this->renderLayout();
    }
    
    /**
     * 
     */
    public function newAction(){
        $this->_forward('edit');
    }
    
    /**
     * show content grids
     */
    public function viewAction(){
        $data = $this->getRequest()->getParams();
        
        if (!$data['content_type']){
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('connector')->__('Please select Content Type'));
            $this->_redirectReferer();
        }
        
        if (!isset($data['locales']) || !is_array($data['locales'])){
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('connector')->__('Please select locales'));
            $this->_redirectReferer();
        }
        
        $contentTypeModel = Mage::getModel('connector/content_types')
                                                ->load( (int) $data['content_type']);
        
        if (!$contentTypeModel->getId()){             
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('connector')->__('Unknown Content Type'));
            $this->_forward("edit");
        }        
        
        $website_id = $this->getRequest()->getParam('website_id');
        Mage::register('sm_website_id', $website_id);
        
        $contentType = Mage::getModel($contentTypeModel->getData('model'))
                                        ->getContentTypeCode();
        
        if (!$contentType || is_null($contentType)){
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('connector')->__('Unknown Content Type'));
            $this->_forward("edit");
        }
        
        $gridBlock = "connector/adminhtml_content_{$contentType}";  
        
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock($gridBlock));
        $this->renderLayout();      
    }
    
    /**
     * Bulk upload single content to Smartling
     * @TODO - implement facade pattern in method body
     * @return void
     */
    public function confirmAction() {
        
        $data = $this->getRequest()->getParams();
        
        $countAddedItem = 0;
        
        //[[ submition validation
        $errors = array();
        if (!isset($data['content_type']) || (string) $data['content_type'] === '') {
            $errors[] = "Content type can't be empty";
        }
        
        $locales = $data['locales'];
        
        if (!is_array($locales)) {
            $errors[] = "Please specify locales";
        }
        
        try {
            
            if (sizeof($errors)) {
                Mage::throwException('Submission has errors');
            }
            
            $contentModelClass = Mage::getModel('connector/content_types')->load($data['content_type']);
            
            if (!$contentModelClass->getId()) {
                $message = Mage::helper('connector')->__("Unknown content type");
                Mage::throwException($message);
            }
            
            $processData = array(
                'type' => $data['content_type']
            ); 
            
            $localeCodes = array();

            /** @var Smartling_Connector_Model_Content_Abstract */
            $projectsLocales = Mage::getModel('connector/projects_locales');

            foreach($locales as $project_id => $locale) {

                $processData['project_id'] = $project_id;

                for ($i = 0; $i < sizeof($locale); $i++) {

                    $localeCode = $projectsLocales->getLocaleCodeByStoreId($locale[$i]);

                    if(!$localeCode) {
                        $errors[] = Mage::helper('connector')
                                ->__("Unknown locale ID - %s", $locale[$i]);
                        continue;
                    }

                    $localeCodes[$locale[$i]] = $localeCode;
                }
            }
           
        
        //]] submition validation
        
            $localesCounter = 0;
            $alreadyAddedItem = 0;
            foreach($locales as $project_id => $locale) {

                $processData['project_id'] = $project_id;

                for ($i = 0; $i < sizeof($locale); $i++) {

                    $localeCode = $localeCodes[$locale[$i]];
                    $localesCounter++;

                    foreach ($data['content'] as $origin_content_id) {

                        $processData['origin_content_id'] = $origin_content_id;

                        $result = Mage::getResourceModel('connector/content')
                                    ->addSingleItem($locale[$i], $processData, true);

                        if ($result == '-1') {
                            $logMessage = Mage::helper('connector')
                                    ->__('Atributes for translation are not specified');
                            Mage::helper('connector')->log($logMessage, Zend_log::ERR);
                        } elseif ($result && $result !== 0) {
                            $countAddedItem++;
                        } elseif ($result == 0) {
                            $logMessage = Mage::helper('connector')
                                    ->__('Item already added in translation queue for locale "%s"', $localeCode);
                            $alreadyAddedItem++;
                        } else {
                            $logMessage = Mage::helper('connector')
                                    ->__('Unable to add Item to translation queue for locale "%s"', $localeCode);
                            Mage::helper('connector')->log($logMessage, Zend_log::ERR);
                        }
                    }

                }
            }

            // update content names
            $typeName = $contentModelClass->getTypeName();
            $contentModel = Mage::getModel('connector/content_' . $typeName);
            $contentModel->saveNames();

            //[[ submition report
            $message = '';
            if($countAddedItem > 1) {
                $message = Mage::helper('connector')->__('Smartling Bulk Upload - %d nodes were added to queue', $countAddedItem);
            } elseif($countAddedItem == 1) {
                $message = Mage::helper('connector')->__('Smartling Bulk Upload - 1 node was added to queue');
            } if(!$countAddedItem && $alreadyAddedItem) {
                $message = Mage::helper('connector')->__('Smartling Bulk Upload - Submited items were added to to queue before.');
            }

            if($message) {
                Mage::getSingleton('adminhtml/session')->addSuccess($message);
                Mage::helper('connector')->log($message, Zend_log::INFO);
            }

            if($countAddedItem && $alreadyAddedItem) {
                $message = Mage::helper('connector')->__('Some submited items were added to to queue before.');
                Mage::getSingleton('adminhtml/session')->addSuccess($message);
                Mage::helper('connector')->log($message, Zend_log::INFO);
            }

            if(($countAddedItem + $alreadyAddedItem) != (count($data['content']) * $localesCounter)) {
                $message = Mage::helper('connector')
                                        ->__('Some items were not added to submition. Please see log for more details.');
                Mage::getSingleton('adminhtml/session')->addError($message);
                Mage::helper('connector')->log($message, Zend_log::ERR);
            }
            //]] submition report
            
            Mage::dispatchEvent('smartling_push_to_queue_after', 
                                            array(
                                                'model_instance' => $contentModel,
                                                'bulk_submit_content' => $data['content']
                                            )
                    );
            
        } catch (Mage_Core_Exception $e) {
          $errors[] = $e->getMessage();  
        } catch (Exception $e) {
          $errors[] = Mage::helper('connector')->__('Sorry. Internal application error.');
        }
            
        if (sizeof($errors) > 0) {
            for ($i = sizeof($errors); $i--;) {
                Mage::helper('connector')->log($errors[$i], Zend_log::ERR);
                Mage::getSingleton('adminhtml/session')->addError($errors[$i]);                        
            }
        }
    
        if($this->getRequest()->isAjax()) {
            $this->loadLayout();
            
            $block = $this->getLayout()->getMessagesBlock()->getGroupedHtml();
            $result = array(
                'messageblock' => $block,
            );  
            
            return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        } else {
            $this->_redirect('*/*/new');
        }
    }
    
    /**
     * 
     * @return \Mage_Core_Controller_Response_Http
     */
    public function profilesAction() {
        
        $type = $this->getRequest()->getParam('type');
        $filter_id = $this->getRequest()->getParam('filter_id');
        
        $localesData = array(
                    'entity_type'     => $type,
                    'filter_id'     => $filter_id
                );

        $locales = 
        $this->getLayout()->createBlock('connector/adminhtml_projects_locales_form', 
                                        'locales', 
                                        $localesData);

        $html = $locales->toHtml() 
                . $locales->getScript();

        if($locales->getReadyStatus()) {

            $continue_button = 
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('catalog')->__('Continue'),
                        'type'   => "button",
                        'class'     => 'save',
                        'onclick' => $locales->getFormName() . 'Form.submit();'
                        ));
            $html .= $continue_button->toHtml();

        }

        return $this->getResponse()->setBody($html);
    }
    
}
