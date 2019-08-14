<?php

/**
 * Description of Types
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Resource_Content_Types 
    extends Mage_Core_Model_Resource_Db_Abstract
{
    
    /**
     * define table and table id
     */
    protected function _construct() {
        $this->_init('connector/content_types', 'type_id');
    }
}

