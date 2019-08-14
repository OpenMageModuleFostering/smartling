<?php

/**
 * Description of Types
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Content_Types
    extends Mage_Core_Model_Abstract
{
    
    protected function _construct() {
        $this->_init('connector/content_types');
    }

     /**
     * 
     * @param string $type
     * @return Mage_Core_Model_Resource_Db_Collection
     */
    public function getTypeDetails($type) {
        $contentType = Mage::getModel('connector/content_types')->getCollection()
                                 ->addFieldToFilter('type_name', array('eq' => $type));                         
        if ($contentType->getSize() == 0){
            Mage::getSingleton('admin/session')->addError(
                    'Content type does not exists'
                    );
        }
        
        $type = $contentType->getFirstItem();
        return $type;
    }
}