<?php

/**
 * Description of Progress
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Grid_Column_Renderer_Progress 
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row){
        
        $data = $row->getData();
        
        $percent = $data[$this->getColumn()->getIndex()];
        
        $elementWraperId = 'status_wraper_' . $row->getContentId();
        $elementProgressId = $this->getColumn()->getId() . '_' . $row->getContentId();
        $elementProgressIdContent = $this->getColumn()->getId() . '_content_' . $row->getContentId();
        
        $loading_style = '';
        $display = '';
        
        if($percent < 100 && $data['status'] != Smartling_Connector_Model_Content::CONTENT_STATUS_NEW) {
            $loading_style = ' bar-loading';
        } else {
            $display = 'display:block;';
        }
        
        $html = "<div id='" . $elementWraperId . "' class='bar-default-wraper{$loading_style}'>"
                . "<div id='" . $elementProgressId . "' class='bar-status' style='width:{$percent}%;{$display}' ></div>"
              . "</div>"
              . "<div id='" . $elementProgressIdContent . "'  class='cell-percent'>" . $percent . "%</div>";        

         return $html;
    }
}