<?php

/**
 * Description of Progress
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Grid_Column_Renderer_Download  
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row){
        
        $data = $row->getData();
        
        if($data['percent'] == 100 || $data['status'] == Smartling_Connector_Model_Content::CONTENT_STATUS_NEW) {
            return '';
        }

        $params = array(
                        'content_id' => $data['content_id'], 
                        'store' => $this->getRequest()->getParam('store')
                    );

        $url = Mage::helper("adminhtml")->getUrl("*/adminhtml_translator/download", $params);
        
        $html = '<a href="' . $url . '" class="download">' 
              . Mage::helper('connector')->__('Download')
              . "</a>";        

         return $html;  
    }
}