<?php

/**
 * Description of CategoryController
 *
 * @author Smartling
 */
class Smartling_Connector_Adminhtml_Translator_CategoryController 
    extends Mage_Adminhtml_Controller_Action
{
    
    /**
     * show submissions in process
     */
    public function indexAction(){
        $this->loadLayout();        
        $this->renderLayout();
    }   
    
    /*
     * use for Ajax only
     */

    public function gridAction(){
        $this->getResponse()->setBody(
                    $this->getLayout()
                         ->createBlock('connector/adminhtml_content_category_grid')
                         ->toHtml()
                );
    }
}
