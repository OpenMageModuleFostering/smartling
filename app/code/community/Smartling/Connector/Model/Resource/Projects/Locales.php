<?php

/**
 * Description of Content
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Resource_Projects_Locales
    extends Mage_Core_Model_Resource_Db_Abstract
{
    
    /**
     * define table and 
     */
    protected function _construct() {
        $this->_init('connector/projects_locales', 'id');
    }
    
}
