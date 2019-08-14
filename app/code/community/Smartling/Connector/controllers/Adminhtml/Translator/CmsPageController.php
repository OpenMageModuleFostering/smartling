<?php


/**
 * Description of CmsPageController
 *
 * @author snail
 */
class Smartling_Connector_Adminhtml_Translator_CmsPageController 
    extends Mage_Adminhtml_Controller_Action
{
        
    public function indexAction(){        
        $this->loadLayout();
//        var_dump(Mage::getSingleton('core/layout')->getUpdate()->getHandles());
//        die();
        $this->renderLayout();
    }  
    
    /*
     * use for Ajax only
     */

    public function gridAction(){
        $this->getResponse()->setBody(
        $this->getLayout()->createBlock('connector/adminhtml_content_cmsPage_grid')
                          ->toHtml()
                );        
    }
    
}
