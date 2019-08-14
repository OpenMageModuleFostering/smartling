<?php
/**
 * Description of Progress
 *
 * @author Smartling
 */
class Smartling_Connector_Block_Adminhtml_Grid_Column_Renderer_Link 
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
  public function render(Varien_Object $row)
  {
    
     $data = $row->getData();
     $contentModel = Mage::getModel('connector/content_' . $data['type_name']);
     
     /**
      * @TODO modify for all types usage
      */
     if( ($contentModel->getContentTypeEntityModel() instanceof Mage_Catalog_Model_Product) == false ) {
         return $data['content_title'];
     }
     
    $url = Mage::helper("adminhtml")->getUrl("adminhtml/catalog_product/edit", array('id' => $data['origin_content_id']));
     
    $value = $row->getData($this->getColumn()->getIndex());
        
        $html = '<a href="' . $url . '" target="_blank">' 
              . $data['content_title'] 
              . "</a>";        

         return $html;  
  }
}