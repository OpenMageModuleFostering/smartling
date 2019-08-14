<?php

/**
 * Description of General
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Types_General extends Smartling_Connector_Model_Types_Xml 
{

    /**
     * 
     * @param string $value
     * @param array $attributes
     * @param string $type
     */
    public function setContent($value, $attributes, $type = 'content') {
        
        Mage::getModel('connector/types_' . $type, array('contentInstance' => $this))
                                ->setContent($value, $attributes);
        
    }
}