<?php

/**
 * Description of ProductController
 *
 * @author Smartling
 */
class Smartling_Connector_Adminhtml_Translator_ProductController 
    extends Mage_Adminhtml_Controller_Action
{
    
    public function indexAction(){
        $this->loadLayout();        
        $this->renderLayout();
    } 
    
    /*
     * use for Ajax only
     */

    public function gridAction(){
        $this->getResponse()->setBody(
        $this->getLayout()->createBlock('connector/adminhtml_content_product_grid')
                          ->toHtml()
                );
    }
}
