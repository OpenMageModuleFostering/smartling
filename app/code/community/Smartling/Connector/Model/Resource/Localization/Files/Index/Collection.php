<?php

/**
 * 
 * @author Smartling
 */
class Smartling_Connector_Model_Resource_Localization_Files_Index_Collection 
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    
    protected function _construct() {
        $this->_init('connector/localization_files_index');
    }
}
