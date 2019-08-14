<?php

/**
 * 
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Source_Log_Level 
{
    
    /**
     * 
     * @return array
     */
    public function toOptionArray() {
        
        $levels = Mage::helper('connector')->getLogLevels();
        $options = array();
        
        foreach ($levels as $value => $label) {
            $options[] = array(
                'value' => $value,
                'label' => $label,
            );
        }
        
        return $options;
    }
}
