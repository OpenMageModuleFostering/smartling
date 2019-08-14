<?php

/**
 * Description of IndexController
 *
 * @author Smartling
 */
class Smartling_Connector_Adminhtml_ProjectsController extends Mage_Adminhtml_Controller_Action 
{

    protected function _initProject() {
        $project = Mage::getModel('connector/projects');
        if ($id = $this->getRequest()->getParam('id')) {
            $project->load($id);
        } else { // set default value
            $project->setRetrievalType(
                        Mage::helper('connector')->getDefaultRetrievalType()
                    );
            $project->setActive(1);
            $project->setApiUrl(Mage::getStoreConfig('connector/api_url'));
        }
        
        
        $localesData = Mage::getModel('connector/projects_locales')
                ->getCollection()
                ->addFieldToFilter('parent_id', $id);
        
        $localesRegistryData = array();
        
        foreach ($localesData as $locale) {
            $localesRegistryData[$locale['store_id']] = $locale;
        }
        
        Mage::register('current_projects', $project);
        Mage::register('current_projects_locales', $localesRegistryData);
        
        return $project;
    }
    
    protected function _initAction() {
        $this->_title($this->__('Smartling'))
                ->_title($this->__('Projects'));

        $this->loadLayout()->_setActiveMenu('smartling/projects');

        return $this;
    }

    public function indexAction() {
        
        $this->_initAction()
                ->_addContent($this->getLayout()->createBlock('connector/adminhtml_projects'))
                ->renderLayout();
    }

    public function newAction() {
        $this->_initProject();
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function viewAction() {
        $this->_forward('edit');
    }
    
    public function editAction() {
        
        $data = $this->getRequest()->getParams();
        $website_id = 0;
        $this->_initProject();
        
        if(isset($data['id']) && !empty($data['id'])) {
            $project = Mage::registry('current_projects');
            $website_id = $project->getData('website_id');
        } elseif($data['website_id']) {
            $website_id = $data['website_id'];
        }
        
        if(!$website_id || !is_numeric($website_id)) {
            $errorMessage = Mage::helper('connector')->__('Please select website for profile');
            Mage::getSingleton('adminhtml/session')->addError($errorMessage);
            return $this->_redirect('*/*/');
        }
        
        Mage::register('website_id', $website_id);
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function saveAction() {
        
        $data = $this->getRequest()->getPost();
        $id = $this->getRequest()->getParam('id');
        
        
        if (!empty($data)) {
        
            try {

                $locale_data = array();
                $adapter = Mage::getModel('core/resource')->getConnection('write');
                
                // select data for mapping
                if (sizeof($data['is_enabled'])) {
                    foreach ($data['is_enabled'] as $key => $value) {

                        if (!$data['locale_code'][$key]) continue;

                        $locale_data[] = array(
                            'store_id' => $key,
                            'locale_code' => $data['locale_code'][$key],
                            'id' => $data['project_locale_identity'][$key]
                        );
                    }
                }

                unset($data['is_enabled']);
                unset($data['locale_code']);

                if (!sizeof($locale_data)) {
                    Mage::throwException($this->__('You should choose at less one locale and enter appropriate Smartling locale code'));
                }
                
                if (!Mage::helper('connector')->validateProjectId($data['project_id'])){
                    Mage::throwException($this->__('Wrong projectId format'));
                }

                if (!Mage::helper('connector')->validateApiKey($data['key'])){
                    Mage::throwException($this->__('Wrong API key format'));
                }

                $adapter->beginTransaction();
                
                // [[ update project main data
                $projects = Mage::getModel('connector/projects');

                $projects->setData($data);
                if ($id) $projects->setId($id);
                
                $projects->save();
                // ]] update project main data

                
                // [[ update project mapping data
                $parent_id = $projects->getId();
                $updatedLocalesIds = array();
                
                foreach ($locale_data as $locale) {
                    $locale['parent_id'] = $parent_id;
                    
                    $projectsLocales = Mage::getModel('connector/projects_locales');
                    if ($locale['id']) $projectsLocales->setId($locale['id']);
                    $projectsLocales->setData($locale)->save();
                    
                    $updatedLocalesIds[] = $projectsLocales->getId();
                }
                
                $projectsLocalesTranslated = Mage::getModel('connector/projects_locales')
                        ->getCollection()
                        ->addFieldToFilter('parent_id', $parent_id)
                        ->addFieldToFilter('id', array('nin' => $updatedLocalesIds));
                
                foreach ($projectsLocalesTranslated as $entity) {
                    $entity->delete();
                }
                
                // ]] update project mapping data
                
                $adapter->commit();

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Data has been saved successfully'));
                
            } catch (Mage_Core_Exception $e) { // validate
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) { // general error
                $adapter->rollback();
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        
        $redirectBack   = $this->getRequest()->getParam('back', false);

        if ($redirectBack) {
            
            if(is_object($projects)) {
                $redirectProjectId = $projects->getId();
            } elseif($id) {
                $redirectProjectId = $id;
            }
            
            return $this->_redirect('*/*/edit', array(
                        'id'    => $redirectProjectId,
                        '_current' => true
                    ));
        } else {
            return $this->_redirect('*/*/');
        }
    }
    
    public function deleteAction() {
        
        $id = $this->getRequest()->getParam('id');
 
        try {
            Mage::getModel('connector/projects')->setId($id)->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Data has been removed successfully'));
        } catch (Exception $e) { // general error
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
 
        $this->_redirectReferer();
    }

}
