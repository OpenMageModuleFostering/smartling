<?php

/**
 * Description of Content
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Resource_Projects
    extends Mage_Core_Model_Resource_Db_Abstract
{
    
    /**
     * define table and 
     */
    protected function _construct() {
        $this->_init('connector/projects', 'id');
    }
    
}
