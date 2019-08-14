<?php

/**
 * Description of Service
 *
 * @author Smartling
 */
class Smartling_Connector_Adminhtml_ServiceController 
    extends Mage_Adminhtml_Controller_Action 
{   
    
    /**
     * 
     * @return Mage_Adminhtml_Model_Session
     */
    protected function _getSession(){
        return Mage::getSingleton('adminhtml/session');
    }
        
    /**
     * 
     * @return string (json)
     */
    public function configTestAction(){
        $requestData = $this->getRequest()->getParams();
        $errors = array();
        $response = array();
                
        if (!isset($requestData['apiKey']) || ($requestData['apiKey']) == ""){                
            $errors[] = Mage::helper('connector')->__('Please Enter Api key');
        }
        
        if (!isset($requestData['projectId']) || ($requestData['projectId']) == ""){
            $errors[] = Mage::helper('connector')->__('Please Enter Project Id');
        }
        
        if (!Mage::helper('connector')->validateProjectId($requestData['projectId'])){
            if(!$requestData['projectId']) {
                $errors[] = Mage::helper('connector')->__('Project Id cannot be empty');
            } else {
                $errors[] = Mage::helper('connector')->__('Wrong projectId format. %s was entered', $requestData['projectId']);
            }
        }
        
        if (!Mage::helper('connector')->validateApiKey($requestData['apiKey'])){
            if(!$requestData['apiKey']) {
                $errors[] = Mage::helper('connector')->__('API key cannot be empty');
            } else {
                $errors[] = Mage::helper('connector')->__('Wrong API key format. %s was entered', $requestData['apiKey']);
            }
        }
        
        
         //try to check connection if errors empty
        if (!sizeof($errors)){
            $options = array(
                'apiKey'    => $requestData['apiKey'],
                'projectId' => $requestData['projectId'],
            );
        
            try{    
                $translator = $this->_getTranslator($options);
                $result = $translator->getTranslationsInfo(null, array('limit' => 1));                
                $result = $translator->convertResponse($result);                
            } catch (Exception $e){
                 $errors = $e->getMessage(); 
            }
        }
        
        //define response depending from response or errors
        if (sizeof($errors) > 0){
            $response = array(
                'result'  => 'ERROR',
                'message' => implode("; ", $errors),
            );
        } else {
            
            if ($result->response->code == "SUCCESS"){
                $response = array (
                    'result'  => 'SUCCESS',
                    'message' => Mage::helper('connector')->__('Your credentials Successfully accepted'),
                );                      
            }  else {
                $response = array (
                    'result'  => 'ERROR',
                    'message' => implode("; ", $result->response->messages),
                 );
            }
        }
        
        $response = Mage::helper('core')->jsonEncode($response);        
        return $this->getResponse()->setBody($response);
        
    }
    
    /**
     * 
     * @param array $options
     * @return \Smartling_Connector_Model_Translator
     */
    protected function _getTranslator($options = array()){
        
        // ini key for object pool if test new connection without real project
        if(!isset($options['project_id'])) {
            $options['project_id'] = 'no_profile_' . time();
        }
        $translator = Mage::getModel('connector/translator', $options);
        $smartlingInstance = $translator->setSmartlingApiInstance($options);
        
        return $smartlingInstance;
    }
}