<?php
class Smartling_Connector_Model_Resource_Translate_Fields_List_Block_Collection 
    extends Smartling_Connector_Model_Resource_Translate_Fields_List_Collection
{
  function __construct() {
      
      $this->_code = 'block';
      $this->_typeName = 'cmsBlock';
      
      $this->_fields = Mage::helper('connector')->getFields('cms_' . $this->_code);
      
      parent::__construct();
  }

}