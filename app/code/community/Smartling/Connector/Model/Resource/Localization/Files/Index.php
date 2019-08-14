<?php

/**
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Resource_Localization_Files_Index
    extends Mage_Core_Model_Resource_Db_Abstract
{
    
    /**
     * define table and 
     */
    protected function _construct() {
        $this->_init('connector/localization_files_index', 'id');
    }
    
}
