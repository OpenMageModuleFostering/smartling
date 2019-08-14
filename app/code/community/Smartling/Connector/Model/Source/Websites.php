<?php

/**
 * Description of Website
 *
 * @author Smartling
 */
class Smartling_Connector_Model_Source_Websites 
{
    /**
     * 
     * @param bool $includeDefault - Use "0" as first item
     * @return array
     */
    public function toOptionArray($includeDefault = false){
        
        $websites = Mage::app()->getWebsites();
        $list = array();
        
        if($includeDefault) {
            $list[] = array(
                'value' => 0,
                'label' => Mage::helper('connector')->__('Please select website to see available profiles')
            );
        }
        
        foreach ($websites as $website) {
            $list[] = array(
                'value' => $website->getWebsiteId(),
                'label' => $website->getName(),
            );
        }
        
        return $list;
    }
}
