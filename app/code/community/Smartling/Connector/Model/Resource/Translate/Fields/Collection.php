<?php

/**
 * Description of Collection
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Resource_Translate_Fields_Collection 
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('connector/translate_fields');
    }
}
